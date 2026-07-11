<?php

namespace App\Roadbook\Model;

final readonly class AdditionalWaypoint
{
    public function __construct(
        public string $title,
        public string $coordinates,
        public string $comment,
    ) {
    }
}
