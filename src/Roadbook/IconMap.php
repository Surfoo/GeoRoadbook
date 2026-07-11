<?php

namespace App\Roadbook;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Maps geocache data to the icon files under /img.
 */
class IconMap
{
    private const CACHE_TYPES = [
        'Traditional Cache' => 'traditional.gif',
        'Multi-cache' => 'multi.gif',
        'Unknown Cache' => 'mystery.gif',
        'Mystery Cache' => 'mystery.gif',
        'Event Cache' => 'event.gif',
        'Webcam Cache' => 'webcam.gif',
        'Wherigo Cache' => 'wherigo.gif',
        'Earthcache' => 'earthcache.gif',
        'Virtual Cache' => 'virtual.gif',
        'Letterbox Hybrid' => 'letterbox.gif',
        'Cache In Trash Out Event' => 'cito.gif',
        'Mega-Event Cache' => 'megaevent.gif',
        'Giga-Event Cache' => 'megaevent.gif',
        'Community Celebration Event' => 'event.gif',
        'Waymark' => 'waymark.gif',
        'Benchmark' => 'benchmark.gif',
    ];

    private const LOG_TYPES = [
        'Found it' => 'icon_smile.png',
        'Needs Maintenance' => 'icon_needsmaint.png',
        "Didn't find it" => 'icon_sad.png',
        'Owner Maintenance' => 'icon_maint.png',
        'Enable Listing' => 'icon_enabled.png',
        'Temporarily Disable Listing' => 'icon_disabled.png',
        'Webcam Photo Taken' => 'icon_camera.png',
        'Update Coordinates' => 'coord_update.png',
        'Publish Listing' => 'icon_greenlight.png',
        'Archive' => 'traffic_cone.png',
        'Announcement' => 'icon_announcement.png',
        'Need Archived' => 'icon_remove.png',
        'Will Attend' => 'icon_rsvp.png',
        'Attended' => 'icon_attended.png',
        'Write note' => 'icon_note.png',
        'Post Reviewer Note' => 'reviewer_note.png',
    ];

    private const CONTAINERS = ['micro', 'small', 'regular', 'large', 'other', 'not chosen', 'not_chosen'];

    /** @var array<string, string> */
    private readonly array $attributeIcons;

    public function __construct(
        #[Autowire('%app.icon_cache_dir%')]
        private readonly string $iconCacheDir,
        #[Autowire('%kernel.project_dir%/config/roadbook/attribute_icons.php')]
        string $attributeIconsFile,
    ) {
        $this->attributeIcons = require $attributeIconsFile;
    }

    public function cacheType(string $type): string
    {
        return sprintf('/img/caches/%s/%s', $this->iconCacheDir, self::CACHE_TYPES[$type] ?? 'unknown.gif');
    }

    /**
     * Star rating image, e.g. 1.5 -> /img/cotation/stars1_5.png. Null for unknown values.
     */
    public function stars(string $rating): ?string
    {
        if (!in_array($rating, ['1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5'], true)) {
            return null;
        }

        return sprintf('/img/cotation/stars%s.png', str_replace('.', '_', $rating));
    }

    public function container(string $container): ?string
    {
        if (!in_array($container, self::CONTAINERS, true)) {
            return null;
        }

        return sprintf('/img/container/%s.gif', str_replace(' ', '_', $container));
    }

    public function attribute(int $id, bool $inc): ?string
    {
        $file = $this->attributeIcons[sprintf('%d-%d', $id, $inc ? 1 : 0)] ?? null;

        return $file === null ? null : '/img/attributes/' . $file;
    }

    /**
     * Null when the log type has no icon: the caller shows the type as text.
     */
    public function logType(string $type): ?string
    {
        $file = self::LOG_TYPES[$type] ?? null;

        return $file === null ? null : '/img/log/' . $file;
    }
}
