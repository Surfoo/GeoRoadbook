<?php

namespace App\Roadbook\Model;

final readonly class Geocache
{
    /**
     * @param list<array{id: int, inc: bool}>         $attributes
     * @param list<GeocacheLog>                       $logs
     * @param list<AdditionalWaypoint>                $waypoints
     * @param list<array{title: string, url: string}> $spoilers
     */
    public function __construct(
        public string $code,
        public string $name,
        public string $type,
        public float $latitude,
        public float $longitude,
        public string $displayCoordinates,
        public string $difficulty,
        public string $terrain,
        public string $container,
        public string $placedBy,
        public ?\DateTimeImmutable $hiddenDate,
        public array $attributes,
        public ?string $description,
        public bool $descriptionIsHtml,
        public ?string $hint,
        public array $logs,
        public array $waypoints,
        public array $spoilers,
    ) {
    }
}
