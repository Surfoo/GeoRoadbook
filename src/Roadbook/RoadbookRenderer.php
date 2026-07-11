<?php

namespace App\Roadbook;

use App\Roadbook\Model\Geocache;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Twig\Environment;

/**
 * Renders parsed geocaches to the roadbook HTML document.
 *
 * Replaces the legacy XSLT transformation; the generated structure and
 * class names are kept identical so roadbook.css, the editor, the table
 * of contents and the PDF pipeline are unaffected.
 */
class RoadbookRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly IconMap $icons,
        private readonly LocaleCatalog $locales,
        private readonly LogTextFormatter $logFormatter,
        #[Autowire(service: 'html_sanitizer.sanitizer.app.description_sanitizer')]
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
    }

    /**
     * @param list<Geocache>             $caches
     * @param array<string, bool|string> $options display_note, display_long_desc, display_hint,
     *                                            display_waypoints, display_spoilers, display_logs, pagebreak
     */
    public function render(array $caches, string $locale, array $options): string
    {
        $items = [];
        foreach ($caches as $cache) {
            $logs = [];
            if (!empty($options['display_logs'])) {
                foreach ($cache->logs as $log) {
                    $logs[] = [
                        'log'  => $log,
                        'html' => $this->sanitizer->sanitize($this->logFormatter->format($log->text)),
                    ];
                }
            }

            $items[] = [
                'cache'       => $cache,
                'description' => $this->displayDescription($cache),
                'hiddenDate'  => $cache->hiddenDate === null ? '' : $this->locales->formatDate($locale, $cache->hiddenDate),
                'logs'        => $logs,
            ];
        }

        return $this->twig->render('roadbook/document.html.twig', [
            'items'   => $items,
            'options' => $options,
            't'       => $this->locales->texts($locale),
            'icons'   => $this->icons,
        ]);
    }

    /**
     * Description as displayed: without the trailing "Additional Waypoints"
     * block (rendered separately) and without HTML comments (Spoiler4Gpx
     * markers and the like), matching the legacy post-processing. Listing
     * HTML is sanitized: scripts, event handlers and javascript: URLs from
     * a crafted GPX must never reach the page.
     */
    private function displayDescription(Geocache $cache): ?string
    {
        if ($cache->description === null) {
            return null;
        }

        if (!$cache->descriptionIsHtml) {
            return nl2br(htmlspecialchars($cache->description, ENT_QUOTES | ENT_SUBSTITUTE), false);
        }

        $html = (string) preg_replace('#<p>Additional (?:Hidden )?Waypoints</p>.*$#is', '', $cache->description);
        $html = (string) preg_replace('#<!--.*-->#msU', '', $html);
        $html = $this->sanitizer->sanitize($html);

        return trim($html) ?: null;
    }
}
