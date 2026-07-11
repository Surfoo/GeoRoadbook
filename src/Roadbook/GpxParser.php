<?php

namespace App\Roadbook;

use App\Roadbook\Model\AdditionalWaypoint;
use App\Roadbook\Model\Geocache;
use App\Roadbook\Model\GeocacheLog;

/**
 * Parses a Groundspeak 1.0.1 GPX document into geocache models.
 */
class GpxParser
{
    private const GROUNDSPEAK_NS = 'http://www.groundspeak.com/cache/1/0/1';

    /**
     * @return list<Geocache>
     */
    public function parse(string $gpx): array
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($gpx);
        } finally {
            libxml_use_internal_errors($previous);
        }
        if ($xml === false) {
            throw new \InvalidArgumentException('Not a valid XML document.');
        }

        $caches = [];
        foreach ($xml->wpt as $wpt) {
            $cache = $wpt->children(self::GROUNDSPEAK_NS)->cache;
            if ($cache === null || $cache->count() === 0) {
                continue; // additional waypoint entries have no cache element
            }

            $latitude = (float) $wpt['lat'];
            $longitude = (float) $wpt['lon'];
            [$description, $isHtml] = $this->extractDescription($cache);

            $caches[] = new Geocache(
                code: trim((string) $wpt->name),
                name: trim((string) $cache->name),
                type: trim((string) $cache->type),
                latitude: $latitude,
                longitude: $longitude,
                displayCoordinates: self::formatCoordinates($latitude, $longitude),
                difficulty: trim((string) $cache->difficulty),
                terrain: trim((string) $cache->terrain),
                container: strtolower(trim((string) $cache->container)),
                placedBy: trim((string) $cache->placed_by),
                hiddenDate: self::parseDate((string) $wpt->time),
                attributes: $this->extractAttributes($cache),
                description: $description,
                descriptionIsHtml: $isHtml,
                hint: trim((string) $cache->encoded_hints) ?: null,
                logs: $this->extractLogs($cache),
                waypoints: $description !== null && $isHtml ? $this->extractWaypoints($description) : [],
                spoilers: $description !== null ? $this->extractSpoilers($description) : [],
            );
        }

        return $caches;
    }

    /**
     * @param list<Geocache> $caches
     *
     * @return list<Geocache>
     */
    public function sort(array $caches, string $sortBy): array
    {
        $comparator = match ($sortBy) {
            'name' => self::textComparator(static fn (Geocache $c) => $c->name),
            'owner' => self::textComparator(static fn (Geocache $c) => $c->placedBy),
            'difficulty' => static fn (Geocache $a, Geocache $b) => (float) $a->difficulty <=> (float) $b->difficulty,
            'terrain' => static fn (Geocache $a, Geocache $b) => (float) $a->terrain <=> (float) $b->terrain,
            default => null,
        };

        if ($comparator !== null) {
            usort($caches, $comparator);
        }

        return $caches;
    }

    private static function textComparator(callable $key): callable
    {
        if (class_exists(\Collator::class)) {
            $collator = new \Collator('root');

            return static fn (Geocache $a, Geocache $b) => $collator->compare($key($a), $key($b));
        }

        return static fn (Geocache $a, Geocache $b) => strcasecmp($key($a), $key($b));
    }

    /**
     * Degrees + decimal minutes, e.g. "N 48° 51.502 E 002° 17.669".
     */
    public static function formatCoordinates(float $latitude, float $longitude): string
    {
        $lat = sprintf(
            '%s %02d° %06.3f',
            $latitude >= 0 ? 'N' : 'S',
            (int) abs($latitude),
            (abs($latitude) - (int) abs($latitude)) * 60,
        );
        $lon = sprintf(
            '%s %03d° %06.3f',
            $longitude >= 0 ? 'E' : 'W',
            (int) abs($longitude),
            (abs($longitude) - (int) abs($longitude)) * 60,
        );

        return $lat . ' ' . $lon;
    }

    private static function parseDate(string $value): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @return array{0: ?string, 1: bool}
     */
    private function extractDescription(\SimpleXMLElement $cache): array
    {
        $node = $cache->long_description;
        $text = trim((string) $node);
        if ($text === '') {
            return [null, false];
        }

        return [$text, (string) $node->attributes()['html'] === 'True'];
    }

    /**
     * @return list<array{id: int, inc: bool}>
     */
    private function extractAttributes(\SimpleXMLElement $cache): array
    {
        $attributes = [];
        foreach ($cache->attributes->attribute ?? [] as $attribute) {
            // id/inc are un-namespaced attributes on a namespaced element
            $raw = $attribute->attributes();
            $attributes[] = [
                'id' => (int) $raw['id'],
                'inc' => (string) $raw['inc'] === '1',
            ];
        }

        return $attributes;
    }

    /**
     * @return list<GeocacheLog>
     */
    private function extractLogs(\SimpleXMLElement $cache): array
    {
        $logs = [];
        foreach ($cache->logs->log ?? [] as $log) {
            $logs[] = new GeocacheLog(
                date: self::parseDate((string) $log->date),
                type: trim((string) $log->type),
                finder: trim((string) $log->finder),
                text: trim((string) $log->text),
            );
        }

        return $logs;
    }

    /**
     * Additional waypoints appended by Groundspeak at the end of the HTML
     * description as "<p>Additional Waypoints</p>" followed by <br />-separated
     * triplets (title, coordinates, comment).
     *
     * @return list<AdditionalWaypoint>
     */
    private function extractWaypoints(string $description): array
    {
        if (!preg_match('#<p>Additional (?:Hidden )?Waypoints</p>#i', $description, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $data = substr($description, $matches[0][1] + strlen($matches[0][0]));
        if ($data === '') {
            return [];
        }

        $lines = explode('<br />', $data);
        array_pop($lines);

        $waypoints = [];
        foreach (array_chunk($lines, 3) as $chunk) {
            if (count($chunk) < 3) {
                continue;
            }

            $coordinates = '';
            if ($chunk[1] !== '' && !str_starts_with($chunk[1], 'N/S')) {
                $coordinates = trim(html_entity_decode($chunk[1]));
            }

            $waypoints[] = new AdditionalWaypoint(
                title: trim((string) preg_replace('/ GC[\w]+/', ' ', $chunk[0])),
                coordinates: $coordinates,
                comment: trim($chunk[2]),
            );
        }

        return $waypoints;
    }

    /**
     * Spoilers4gpx embeds images as "<!-- Spoiler4Gpx [title](url) -->".
     *
     * @return list<array{title: string, url: string}>
     */
    private function extractSpoilers(string $description): array
    {
        if (!preg_match_all('/<!-- Spoiler4Gpx \[([^]]*)\]\(([^)]*)\) -->/', $description, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return array_map(
            static fn (array $m) => ['title' => $m[1], 'url' => $m[2]],
            $matches,
        );
    }
}
