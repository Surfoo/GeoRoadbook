<?php

namespace App\Roadbook;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Reads the roadbook locale files (config/locales/*.xml).
 */
class LocaleCatalog
{
    /** @var array<string, array{texts: array<string, string>, dateFormat: string}> */
    private array $catalogs = [];

    public function __construct(
        #[Autowire('%app.locales_dir%')]
        private readonly string $localesDir,
    ) {
    }

    public function text(string $locale, string $id): string
    {
        return $this->load($locale)['texts'][$id] ?? $id;
    }

    /**
     * Formats a date with the locale's legacy pattern (%d, %m, %y, %Y, %%).
     */
    public function formatDate(string $locale, \DateTimeImmutable $date): string
    {
        return (string) preg_replace_callback(
            '/%(.)/',
            static fn (array $m) => match ($m[1]) {
                'd' => $date->format('d'),
                'm' => $date->format('m'),
                'y' => $date->format('y'),
                'Y' => $date->format('Y'),
                '%' => '%',
                default => $m[0],
            },
            $this->load($locale)['dateFormat'],
        );
    }

    /**
     * @return array{texts: array<string, string>, dateFormat: string}
     */
    private function load(string $locale): array
    {
        if (isset($this->catalogs[$locale])) {
            return $this->catalogs[$locale];
        }

        $file = $this->localesDir . '/' . basename($locale) . '.xml';
        if (!is_readable($file)) {
            throw new \InvalidArgumentException(sprintf('Unknown roadbook locale "%s".', $locale));
        }

        $xml = simplexml_load_file($file);
        $texts = [];
        foreach ($xml->text as $text) {
            $texts[(string) $text->attributes()['id']] = (string) $text;
        }

        return $this->catalogs[$locale] = [
            'texts' => $texts,
            'dateFormat' => (string) ($xml->format ?? '%Y/%m/%d'),
        ];
    }
}
