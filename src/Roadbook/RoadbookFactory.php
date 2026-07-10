<?php

namespace App\Roadbook;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

class RoadbookFactory
{
    public function __construct(
        #[Autowire('%app.roadbook_dir%')]
        private readonly string $roadbookDir,
        #[Autowire('%app.xslt_dir%')]
        private readonly string $xsltDir,
        #[Autowire('%app.locales_dir%')]
        private readonly string $localesDir,
        #[Autowire('%app.icon_cache_dir%')]
        private readonly string $iconCacheDir,
        private readonly Environment $twig,
    ) {
    }

    public function create(?string $id = null): Roadbook
    {
        if (!is_dir($this->roadbookDir)) {
            mkdir($this->roadbookDir, 0775, true);
        }

        return new Roadbook(
            $this->roadbookDir,
            $this->xsltDir,
            $this->localesDir,
            $this->iconCacheDir,
            $this->twig,
            $id,
        );
    }
}
