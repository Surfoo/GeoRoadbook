<?php

/**
 * Roadbook generation engine, ported from the legacy Silex app
 * (src/Georoadbook/Georoadbook.php on the master branch).
 *
 * @author  Surfoo <surfooo@gmail.com>
 *
 * @link    https://github.com/Surfoo/georoadbook
 *
 * @license http://opensource.org/licenses/eclipse-2.0.php
 */

namespace App\Roadbook;

use Twig\Environment;

class Roadbook
{
    public const ID_LENGTH = 7;

    public string $id;

    public ?string $gpx = null;

    public ?string $html = null;

    protected ?string $locale = null;

    protected array $bbcodeSmileys = [
        ':)'  => 'icon_smile.gif',
        ':D'  => 'icon_smile_big.gif',
        '8D'  => 'icon_smile_cool.gif',
        ':I'  => 'icon_smile_blush.gif',
        ':P'  => 'icon_smile_tongue.gif',
        '}:)' => 'icon_smile_evil.gif',
        ';)'  => 'icon_smile_wink.gif',
        ':o)' => 'icon_smile_clown.gif',
        'B)'  => 'icon_smile_blackeye.gif',
        '8'   => 'icon_smile_8ball.gif',
        ':('  => 'icon_smile_sad.gif',
        '8)'  => 'icon_smile_shy.gif',
        ':O'  => 'icon_smile_shock.gif',
        ':(!' => 'icon_smile_angry.gif',
        'xx(' => 'icon_smile_dead.gif',
        '|)'  => 'icon_smile_sleepy.gif',
        ':X'  => 'icon_smile_kisses.gif',
        '^'   => 'icon_smile_approve.gif',
        'V'   => 'icon_smile_dissapprove.gif',
        '?'   => 'icon_smile_question.gif',
    ];

    protected array $bbcodeColors = [
        'black', 'blue', 'gold', 'green', 'maroon', 'navy', 'orange',
        'pink', 'purple', 'red', 'teal', 'white', 'yellow',
    ];

    public function __construct(
        private readonly string $roadbookDir,
        private readonly string $xsltDir,
        private readonly string $localesDir,
        private readonly string $iconCacheDir,
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
     * Renders the raw roadbook page to PDF through the WeasyPrint service.
     *
     * @throws \RuntimeException when the conversion fails
     */
    public function exportPdf(string $internalBaseUrl, string $weasyprintUrl): void
    {
        $pdfDir = dirname($this->getPdfFile());
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0775, true);
        }

        $url = rtrim($internalBaseUrl, '/') . '/roadbook/' . $this->id . '/raw';

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode(['url' => $url]),
                'timeout' => 120,
                'ignore_errors' => true,
            ],
        ]);

        $body = file_get_contents(rtrim($weasyprintUrl, '/') . '/convert', false, $context);
        $status = 0;
        foreach ($http_response_header ?? [] as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
                $status = (int) $m[1];
            }
        }

        if ($body === false || $status !== 200) {
            $error = 'PDF conversion failed';
            if (is_string($body) && ($decoded = json_decode($body, true)) && isset($decoded['error'])) {
                $error .= ': ' . $decoded['error'];
            }

            throw new \RuntimeException($error);
        }

        if (!$this->saveFile($this->getPdfFile(), $body)) {
            throw new \RuntimeException('Unable to write the PDF file.');
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

        $html = $this->twig->render('raw.twig.html', [
            'suffix_css_js' => '',
            'style' => $this->getCustomCss(),
            'content' => file_get_contents($this->getHtmlFile()),
        ]);
        $zip->addFromString('roadbook/' . $this->id . '.html', $html);
        $zip->addFile($publicDir . '/design/roadbook.css', 'design/roadbook.css');

        foreach (['img', 'images'] as $imageDir) {
            $dir = $publicDir . '/' . $imageDir;
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                $zip->addFile($file->getPathname(), $imageDir . '/' . substr($file->getPathname(), strlen($dir) + 1));
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

        @unlink($this->getPdfFile());

        return true;
    }

    public function getLastSavedDate(): string
    {
        return date('Y-m-d H:i:s', filemtime($this->getHtmlFile()));
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
            $cssOptions['margin_top'] ?? 10,
            $cssOptions['margin_right'] ?? 10,
            $cssOptions['margin_bottom'] ?? 10,
            $cssOptions['margin_left'] ?? 10,
        );

        if (!empty($cssOptions['header_pagination'])) {
            $pageOptions .= sprintf('@top-%s{content:counter(page)}', $cssOptions['header_align'] ?? 'left');
        } elseif (!empty($cssOptions['header_text'])) {
            $pageOptions .= sprintf('@top-%s{content:"%s"}', $cssOptions['header_align'] ?? 'left', htmlspecialchars($cssOptions['header_text']));
        }
        if (!empty($cssOptions['footer_pagination'])) {
            $pageOptions .= sprintf('@bottom-%s{content:counter(page)}', $cssOptions['footer_align'] ?? 'left');
        } elseif (!empty($cssOptions['footer_text'])) {
            $pageOptions .= sprintf('@bottom-%s{content:"%s"}', $cssOptions['footer_align'] ?? 'left', htmlspecialchars($cssOptions['footer_text']));
        }

        return sprintf('@page{%s}', $pageOptions);
    }

    public function saveOptions(array $options): bool
    {
        return $this->saveFile($this->getJsonFile(), json_encode($options));
    }

    public function saveFile(string $filename, string $content = ''): bool
    {
        return file_put_contents($filename, $content) !== false;
    }

    public function convertXmlToHtml(string $locale, array $options): static
    {
        $this->locale = $locale;

        $xsldoc = new \DOMDocument();
        $xsldoc->load($this->xsltDir . '/roadbook.xslt');
        $xsl = new \XSLTProcessor();
        $xsl->importStyleSheet($xsldoc);
        $xsl->setParameter('', 'locale_filename', $this->getLocaleFile());
        $xsl->setParameter('', 'icon_cache_dir', $this->iconCacheDir);
        $xsl->setParameter('', $options);

        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $this->html = $xsl->transformToXML($xml);
        $this->html = preg_replace('/<\?xml[^>]*\?>/i', '', $this->html);
        $this->html = htmlspecialchars_decode($this->html);

        // Remove comments
        $this->html = preg_replace('#<!--.*-->#msU', '', $this->html);

        // Remove waypoints
        $this->html = preg_replace('#<p>Additional [Hidden\s]+?Waypoints</p>.*(</div>)#msU', '$1', $this->html);

        return $this;
    }

    public function cleanHtml(): static
    {
        if ($this->html === null || !class_exists(\tidy::class)) {
            return $this;
        }

        // http://tidy.sourceforge.net/docs/quickref.html
        $config = [
            'doctype' => 'html',
            'output-xhtml' => true,
            'wrap' => 0,
        ];
        $tidy = new \tidy();
        $tidy->parseString($this->html, $config, 'utf8');
        $tidy->cleanRepair();
        $this->html = (string) $tidy;

        return $this;
    }

    public function getOnlyBody(): void
    {
        if (preg_match('/<body>(.*)<\/body>/msU', $this->html, $match)) {
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

        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheTitle ')]");
        $gccodeNode = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheGCode ')]");

        $tocContent = [];
        foreach ($nodes as $node) {
            $tocContent[] = [
                'icon' => $node->firstChild->getAttribute('src'),
                'gccode' => false,
                'title' => $node->textContent,
            ];
        }
        foreach ($gccodeNode as $key => $node) {
            $tocContent[$key]['gccode'] = $node->textContent;
        }

        if (!empty($tocContent)) {
            $toc = new \DOMDocument();
            $toc->load($this->getLocaleFile());
            $xPath = new \DOMXPath($toc);
            $tocI18n['title'] = $xPath->query("text[@id='toc_title']")->item(0)->nodeValue;
            $tocI18n['name'] = $xPath->query("text[@id='toc_name']")->item(0)->nodeValue;
            $tocI18n['page'] = $xPath->query("text[@id='toc_page']")->item(0)->nodeValue;

            $tocHtml = $this->twig->render('toc.twig.html', [
                'i18n' => $tocI18n,
                'content' => $tocContent,
            ]);

            $frag = $dom->createDocumentFragment();
            $frag->appendXML($tocHtml);

            $body = $dom->getElementsByTagName('body')->item(0);
            $first = $body->getElementsByTagName('div')->item(0);
            $body->insertBefore($frag, $first);
            $this->html = $dom->saveHtml();
        }
    }

    public function removeImages(bool $displayShortDesc): void
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        if ($displayShortDesc) {
            $finder = new \DOMXPath($dom);
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' short_description ')]");
            foreach ($nodes as $node) {
                $this->removeChildImages($node);
            }

            $this->html = $dom->saveHtml();
        }

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' long_description ')]");
        foreach ($nodes as $node) {
            $this->removeChildImages($node);
        }

        $this->html = $dom->saveHtml();
    }

    private function removeChildImages(\DOMNode $node): void
    {
        $toRemove = iterator_to_array($node->getElementsByTagName('img'));
        foreach ($toRemove as $img) {
            $img->parentNode->removeChild($img);
        }
    }

    public function addSpoilers(): void
    {
        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $waypoints = $xml->getElementsByTagName('wpt');
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        foreach ($waypoints as $waypoint) {
            $longDescription = $waypoint->getElementsByTagNameNS('http://www.groundspeak.com/cache/1/0/1', 'long_description');
            if (empty($longDescription->length)) {
                continue;
            }
            if (preg_match_all('/<!-- Spoiler4Gpx \[([^]]*)\]\(([^)]*)\) -->/', $longDescription->item(0)->nodeValue, $spoilers, PREG_SET_ORDER)) {
                $gccode = $waypoint->getElementsByTagName('name')->item(0)->nodeValue;
                foreach ($spoilers as $spoiler) {
                    $nodes = $finder->query("//div[@data-cache-id='" . $gccode . "']/div[@class='cacheSpoilers']");
                    if (empty($nodes->length)) {
                        continue;
                    }
                    $frag = $dom->createDocumentFragment();
                    $frag->appendXML('<p>Spoilers</p>' . "\n");
                    foreach ($nodes as $node) {
                        $node->appendChild($frag);
                        $frag = $dom->createDocumentFragment();
                        $frag->appendXML('<![CDATA[<img src="' . $spoiler[2] . '" alt="' . $spoiler[1] . '"/><br />' . "\n]]>");
                        $node->appendChild($frag);
                    }
                }
            }
        }
        $this->html = $dom->saveHtml();
    }

    public function addWaypoints(): void
    {
        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $waypoints = $xml->getElementsByTagName('wpt');
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        foreach ($waypoints as $waypoint) {
            $longDescription = $waypoint->getElementsByTagNameNS('http://www.groundspeak.com/cache/1/0/1', 'long_description');
            if (empty($longDescription->length)) {
                continue;
            }

            if (!preg_match('#<p>Additional [Hidden\s]+?Waypoints</p>#i', $longDescription->item(0)->nodeValue, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $data = substr($longDescription->item(0)->nodeValue, $matches[0][1] + strlen($matches[0][0]));
            if (!$data) {
                continue;
            }

            $detailsWaypoints = explode('<br />', $data);
            array_pop($detailsWaypoints);
            $detailsWaypoints = array_chunk($detailsWaypoints, 3);

            $gccode = $waypoint->getElementsByTagName('name')->item(0)->nodeValue;
            $nodes = $finder->query("//div[@data-cache-id='" . $gccode . "']//*[@class='cacheWaypoints']");
            $frag = $dom->createDocumentFragment();
            $frag->appendXML('<p>Waypoints</p>' . "\n");

            if (!empty($nodes->length)) {
                $nodes->item(0)->appendChild($frag);
            }

            foreach ($detailsWaypoints as $wptData) {
                $title = preg_replace('/ GC[\w]+/', ' ', $wptData[0]);
                $coordinates = '';
                if ($wptData[1] !== '' && strpos($wptData[1], 'N/S') !== 0) {
                    $coordinates = ' - ' . trim(html_entity_decode($wptData[1]));
                }
                $comment = $wptData[2];

                $fragWpt = $dom->createDocumentFragment();
                $fragWpt->appendXML('<![CDATA[<p><strong>' . $title . $coordinates . '</strong><br />' . $comment . "</p>\n]]>");

                if (!empty($nodes->length)) {
                    $nodes->item(0)->appendChild($fragWpt);
                }
            }
        }
        $this->html = $dom->saveHtml();
    }

    public function encryptHints(): void
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheHintContent ')]");
        foreach ($nodes as $node) {
            $chars = str_split($node->textContent);
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

    public function parseMarkdown(): static
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheLogText ')]");

        $mdParser = new \cebe\markdown\Markdown();
        foreach ($nodes as $node) {
            $rawLog = $node->ownerDocument->saveHTML($node);
            $rawLog = trim(str_replace(['<td class="cacheLogText" colspan="2">', '</td>'], '', $rawLog));
            $log = preg_replace('/<br>$/', '', $rawLog);

            $node->nodeValue = $mdParser->parse($log);
        }

        $this->html = htmlspecialchars_decode($dom->saveHtml());

        return $this;
    }

    public function parseBBcode(): static
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheLogText ')]");

        $parser = new \JBBCode\Parser();
        $parser->addCodeDefinitionSet(new \JBBCode\DefaultCodeDefinitionSet());

        foreach ($this->bbcodeColors as $color) {
            $builder = new \JBBCode\CodeDefinitionBuilder($color, '<span style="color: ' . $color . ';">{param}</span>');
            $parser->addCodeDefinition($builder->build());
        }

        foreach ($nodes as $node) {
            $rawLog = $node->ownerDocument->saveHTML($node);
            $rawLog = trim(str_replace(['<td class="cacheLogText" colspan="2">', '</td>'], '', $rawLog));
            $log = preg_replace('/<br>$/', '', $rawLog);
            $parser->parse($log);
            $node->nodeValue = $parser->getAsHtml();
        }

        $this->html = htmlspecialchars_decode($dom->saveHtml());

        $bbcodes = array_keys($this->bbcodeSmileys);
        $images = array_values($this->bbcodeSmileys);
        foreach ($images as $k => &$image) {
            $image = '<img src="../images/icons/' . $image . '" alt="' . $bbcodes[$k] . '" />';
        }
        foreach ($bbcodes as &$bbcode) {
            $bbcode = '[' . $bbcode . ']';
        }

        $this->html = str_replace($bbcodes, $images, $this->html);

        return $this;
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
