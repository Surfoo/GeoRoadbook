<?php

namespace App\Roadbook\Model;

final readonly class GeocacheLog
{
    public function __construct(
        public ?\DateTimeImmutable $date,
        public string $type,
        public string $finder,
        public string $text,
    ) {
    }
}
