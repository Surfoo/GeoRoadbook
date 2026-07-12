<?php

/**
 * Roadbook generation engine, ported from the legacy Silex app
 * (src/Georoadbook/Georoadbook.php on the master branch).
 *
 * @author  Surfoo <surfooo@gmail.com>
 *
 * @see    https://github.com/Surfoo/georoadbook
 *
 * @license http://opensource.org/licenses/eclipse-2.0.php
 */

namespace App\Roadbook;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Twig\Environment;

class Roadbook
{
    public const ID_LENGTH = 7;

    public string $id;

    public ?string $gpx = null;

    public ?string $html = null;

    protected ?string $locale = null;

    public function __construct(
        private readonly string $roadbookDir,
        private readonly string $localesDir,
        private readonly Environment $twig,
        ?string $id = null,
    ) {
        if ($id !== null && !ctype_alnum($id)) {
            throw new \InvalidArgumentException('Invalid roadbook id.');
        }
        $this->id = $id !== null ? basename($id) : self::generateId();
    }

    public function getRoadbookPath(): string
    {
        return $this->roadbookDir . sprintf('/%s', $this->id);
    }

    public function getGpxFile(): string
    {
        return $this->roadbookDir . sprintf('/%s.gpx', $this->id);
    }

    public function getHtmlFile(): string
    {
        return $this->roadbookDir . sprintf('/%s.html', $this->id);
    }

    public function getJsonFile(): string
    {
        return $this->roadbookDir . sprintf('/%s.json', $this->id);
    }

    public function getPdfFile(): string
    {
        return $this->roadbookDir . sprintf('/pdf/%s.pdf', $this->id);
    }

    /**
     * Saves the cover page image (event flyer, logo, etc.) and returns its
     * web-accessible path. public/roadbook is served directly by nginx, so
     * no dedicated route is needed to display it (raw view, PDF export,
     * zip export all reach it the same way as /img and /images assets).
     */
    public function saveCoverImage(string $binary, string $extension): string
    {
        $filename = sprintf('%s-cover.%s', $this->id, $extension);
        $this->saveFile($this->roadbookDir . '/' . $filename, $binary);

        return '/roadbook/' . $filename;
    }

    /**
     * Finds the cover image previously saved by saveCoverImage(), if any.
     */
    private function findCoverImageFile(): ?string
    {
        $matches = glob($this->roadbookDir . '/' . $this->id . '-cover.*');

        return $matches !== false && $matches !== [] ? $matches[0] : null;
    }

    /**
     * Renders the raw roadbook page to PDF, either through the WeasyPrint
     * HTTP sidecar (dev/Docker) or the standalone `weasyprint` binary reading
     * the rendered HTML straight off disk (production, when $weasyprintUrl
     * is "cli" — no network hop, so $internalBaseUrl/DNS don't matter there).
     *
     * @throws \RuntimeException when the conversion fails
     */
    public function exportPdf(string $internalBaseUrl, string $weasyprintUrl, string $weasyprintBin = 'weasyprint', ?string $publicDir = null): void
    {
        $pdfDir = dirname($this->getPdfFile());
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0775, true);
        }

        if ($weasyprintUrl === 'cli') {
            if ($publicDir === null) {
                throw new \RuntimeException('publicDir is required for CLI-mode PDF export.');
            }
            $body = $this->convertPdfViaCli($publicDir, $weasyprintBin);
        } else {
            $url  = rtrim($internalBaseUrl, '/') . '/roadbook/' . $this->id . '/raw';
            $body = $this->convertPdfViaHttp($url, $weasyprintUrl);
        }

        if (!$this->saveFile($this->getPdfFile(), $body)) {
            throw new \RuntimeException('Unable to write the PDF file.');
        }
    }

    private function convertPdfViaHttp(string $url, string $weasyprintUrl): string
    {
        $convertUrl = rtrim($weasyprintUrl, '/') . '/convert';

        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\n",
                'content'       => json_encode(['url' => $url]),
                'timeout'       => 120,
                'ignore_errors' => true,
            ],
        ]);

        $body   = @file_get_contents($convertUrl, false, $context);
        $status = 0;
        foreach (http_get_last_response_headers() ?? [] as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
                $status = (int) $m[1];
            }
        }

        if ($body === false || $status !== 200) {
            $detail = null;
            if (is_string($body) && ($decoded = json_decode($body, true)) && isset($decoded['error'])) {
                $detail = $decoded['error'];
            } elseif (is_string($body) && $body !== '') {
                $detail = substr($body, 0, 500);
            } elseif ($body === false) {
                $detail = error_get_last()['message'] ?? 'no response from WeasyPrint';
            }

            throw new \RuntimeException(sprintf('PDF conversion failed (weasyprint=%s, raw_url=%s, status=%d)%s', $convertUrl, $url, $status, $detail !== null ? ': ' . $detail : ''));
        }

        return $body;
    }

    private function convertPdfViaCli(string $publicDir, string $weasyprintBin): string
    {
        $html = $this->twig->render('raw.twig.html', [
            'suffix_css_js' => '',
            'style_css'     => $this->getThemeCss(),
            'style'         => $this->getCustomCss(),
            'content'       => (string) file_get_contents($this->getHtmlFile()),
        ]);

        $htmlFile   = tempnam(sys_get_temp_dir(), 'weasyprint_src_') . '.html';
        $outputFile = tempnam(sys_get_temp_dir(), 'weasyprint_out_');

        try {
            file_put_contents($htmlFile, $html);

            // Rendered from disk directly: relative/absolute asset paths (/design,
            // /img, /images) resolve against the local filesystem, no HTTP round-trip.
            $baseUrl = 'file://' . rtrim($publicDir, '/') . '/';

            $process = new Process([$weasyprintBin, $htmlFile, $outputFile, '--base-url', $baseUrl], timeout: 120);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException(sprintf('PDF conversion failed (weasyprint_bin=%s, roadbook_id=%s): %s', $weasyprintBin, $this->id, trim($process->getErrorOutput()) !== '' ? trim($process->getErrorOutput()) : trim($process->getOutput())), previous: new ProcessFailedException($process));
            }

            $body = file_get_contents($outputFile);
            if ($body === false || $body === '') {
                throw new \RuntimeException(sprintf('PDF conversion produced an empty file (weasyprint_bin=%s, roadbook_id=%s)', $weasyprintBin, $this->id));
            }

            return $body;
        } finally {
            @unlink($htmlFile);
            @unlink($outputFile);
        }
    }

    /**
     * Builds a zip archive with the rendered HTML, stylesheet and images.
     * Returns the archive path.
     */
    public function buildZip(string $publicDir): string
    {
        $zipFile = sys_get_temp_dir() . sprintf('/roadbook-%s.zip', $this->id);
        @unlink($zipFile);

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Unable to create the zip archive.');
        }

        // The generated HTML uses absolute asset paths (/img, /images, /roadbook);
        // the archive is self-contained, so rewrite them relative to its layout.
        $content = str_replace(
            ['src="/img/', 'src="/images/', 'src="/roadbook/'],
            ['src="../img/', 'src="../images/', 'src="'],
            (string) file_get_contents($this->getHtmlFile()),
        );

        $themeCss = $this->getThemeCss();

        $html = $this->twig->render('raw.twig.html', [
            'suffix_css_js' => '',
            'asset_prefix'  => '..',
            'style_css'     => $themeCss,
            'style'         => $this->getCustomCss(),
            'content'       => $content,
        ]);
        $zip->addFromString('roadbook/' . $this->id . '.html', $html);
        $zip->addFile($publicDir . '/design/' . $themeCss, 'design/' . $themeCss);

        $coverImage = $this->findCoverImageFile();
        if ($coverImage !== null) {
            $zip->addFile($coverImage, 'roadbook/' . basename($coverImage));
        }

        foreach (['img', 'images'] as $imageDir) {
            $dir = $publicDir . '/' . $imageDir;
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                $zip->addFile($file->getPathname(), $imageDir . '/' . substr((string) $file->getPathname(), strlen($dir) + 1));
            }
        }

        $zip->close();

        return $zipFile;
    }

    public function create(string $gpx): bool
    {
        $this->gpx = $gpx;

        return $this->saveFile($this->getGpxFile(), $this->gpx);
    }

    public function delete(): bool
    {
        $pattern = $this->roadbookDir . '/' . $this->id . '.*';

        foreach (glob($pattern) as $file) {
            @unlink($file);
        }

        $coverImage = $this->findCoverImageFile();
        if ($coverImage !== null) {
            @unlink($coverImage);
        }

        @unlink($this->getPdfFile());

        return true;
    }

    public function getLastSavedDate(): string
    {
        return date('Y-m-d H:i:s', filemtime($this->getHtmlFile()));
    }

    public function getThemeCss(): string
    {
        if (!is_readable($this->getJsonFile())) {
            return 'roadbook.css';
        }
        $options = json_decode((string) file_get_contents($this->getJsonFile()), true);

        return is_array($options) && isset($options['theme_css']) && is_string($options['theme_css'])
            ? $options['theme_css']
            : 'roadbook.css';
    }

    public function getCustomCss(): string
    {
        if (!is_readable($this->getJsonFile())) {
            return '';
        }
        $cssOptions = json_decode(file_get_contents($this->getJsonFile()), true);
        if (!is_array($cssOptions) || $cssOptions === []) {
            return '';
        }

        $pageOptions = sprintf('size: %s %s;', $cssOptions['page_size'] ?? 'A4', $cssOptions['orientation'] ?? 'portrait');
        $pageOptions .= sprintf(
            'margin: %dmm %dmm %dmm %dmm;',
            $cssOptions['margin_top']    ?? 10,
            $cssOptions['margin_right']  ?? 10,
            $cssOptions['margin_bottom'] ?? 10,
            $cssOptions['margin_left']   ?? 10,
        );

        if (!empty($cssOptions['header_pagination'])) {
            $pageOptions .= sprintf('@top-%s{content:counter(page)}', $cssOptions['header_align'] ?? 'left');
        } elseif (!empty($cssOptions['header_text'])) {
            $pageOptions .= sprintf('@top-%s{content:"%s"}', $cssOptions['header_align'] ?? 'left', htmlspecialchars((string) $cssOptions['header_text']));
        }
        if (!empty($cssOptions['footer_pagination'])) {
            $pageOptions .= sprintf('@bottom-%s{content:counter(page)}', $cssOptions['footer_align'] ?? 'left');
        } elseif (!empty($cssOptions['footer_text'])) {
            $pageOptions .= sprintf('@bottom-%s{content:"%s"}', $cssOptions['footer_align'] ?? 'left', htmlspecialchars((string) $cssOptions['footer_text']));
        }

        return sprintf('@page{%s}', $pageOptions);
    }

    /**
     * @param array<string, bool|int|string> $options
     */
    public function saveOptions(array $options): bool
    {
        return $this->saveFile($this->getJsonFile(), json_encode($options));
    }

    public function saveFile(string $filename, string $content = ''): bool
    {
        return file_put_contents($filename, $content) !== false;
    }

    /**
     * Sets the rendered roadbook document (from RoadbookRenderer) and the
     * locale used by later passes (table of contents).
     */
    public function setContent(string $html, string $locale): static
    {
        $this->html   = $html;
        $this->locale = $locale;

        return $this;
    }

    public function cleanHtml(): static
    {
        if ($this->html === null || !class_exists(\tidy::class)) {
            return $this;
        }

        // http://tidy.sourceforge.net/docs/quickref.html
        $config = [
            'doctype'      => 'html5',
            'output-xhtml' => true,
            'wrap'         => 0,
        ];
        $tidy = new \tidy();
        $tidy->parseString($this->html, $config, 'utf8');
        $tidy->cleanRepair();
        $this->html = tidy_get_output($tidy);

        return $this;
    }

    public function getOnlyBody(): void
    {
        if (preg_match('/<body>(.*)<\/body>/msU', (string) $this->html, $match)) {
            $this->html = $match[1];
        }
    }

    public function addToc(): void
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);

        $nodes      = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheTitle ')]");
        $gccodeNode = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheGCode ')]");

        $tocContent = [];
        foreach ($nodes as $node) {
            $icon = $node->firstChild instanceof \DOMElement ? $node->firstChild->getAttribute('src') : '';

            $tocContent[] = [
                'icon'   => $icon,
                'gccode' => false,
                'title'  => $node->textContent,
            ];
        }
        foreach ($gccodeNode as $key => $node) {
            $tocContent[$key]['gccode'] = $node->textContent;
        }

        if (!empty($tocContent)) {
            $toc = new \DOMDocument();
            $toc->load($this->getLocaleFile());
            $xPath            = new \DOMXPath($toc);
            $tocI18n['title'] = $xPath->query("text[@id='toc_title']")->item(0)->nodeValue;
            $tocI18n['name']  = $xPath->query("text[@id='toc_name']")->item(0)->nodeValue;
            $tocI18n['page']  = $xPath->query("text[@id='toc_page']")->item(0)->nodeValue;

            $tocHtml = $this->twig->render('toc.twig.html', [
                'i18n'    => $tocI18n,
                'content' => $tocContent,
            ]);

            $frag = $dom->createDocumentFragment();
            $frag->appendXML($tocHtml);

            $body = $dom->getElementsByTagName('body')->item(0);
            if ($body instanceof \DOMElement) {
                $body->insertBefore($frag, $body->getElementsByTagName('div')->item(0));
            }
            $this->html = $dom->saveHtml();
        }
    }

    public function removeImages(): void
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes  = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' long_description ')]");
        foreach ($nodes as $node) {
            $this->removeChildImages($node);
        }

        $this->html = $dom->saveHtml();
    }

    private function removeChildImages(\DOMNode $node): void
    {
        if (!$node instanceof \DOMElement) {
            return;
        }
        foreach (iterator_to_array($node->getElementsByTagName('img')) as $img) {
            $img->parentNode?->removeChild($img);
        }
    }

    public function encryptHints(): void
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes  = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheHintContent ')]");
        foreach ($nodes as $node) {
            $chars  = str_split($node->textContent);
            $encode = true;
            foreach ($chars as &$char) {
                if ($char === '[') {
                    $encode = false;
                    continue;
                }
                if ($char === ']') {
                    $encode = true;
                    continue;
                }
                if ($encode) {
                    $char = str_rot13($char);
                }
            }
            $node->nodeValue = implode('', $chars);
        }
        $this->html = $dom->saveHtml();
    }

    protected static function generateId(): string
    {
        return substr(bin2hex(random_bytes(8)), 0, self::ID_LENGTH);
    }

    protected function getLocaleFile(): string
    {
        return $this->localesDir . sprintf('/%s.xml', $this->locale);
    }
}
