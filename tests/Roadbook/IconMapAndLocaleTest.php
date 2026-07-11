<?php

namespace App\Tests\Roadbook;

use App\Roadbook\IconMap;
use App\Roadbook\LocaleCatalog;
use PHPUnit\Framework\TestCase;

class IconMapAndLocaleTest extends TestCase
{
    private IconMap $icons;
    private LocaleCatalog $locales;

    protected function setUp(): void
    {
        $projectDir    = dirname(__DIR__, 2);
        $this->icons   = new IconMap('32x32', $projectDir . '/config/roadbook/attribute_icons.php');
        $this->locales = new LocaleCatalog($projectDir . '/config/locales');
    }

    public function testCacheTypeIcons(): void
    {
        $this->assertSame('/img/caches/32x32/traditional.gif', $this->icons->cacheType('Traditional Cache'));
        $this->assertSame('/img/caches/32x32/unknown.gif', $this->icons->cacheType('Some Future Type'));
    }

    public function testStars(): void
    {
        $this->assertSame('/img/cotation/stars1_5.png', $this->icons->stars('1.5'));
        $this->assertSame('/img/cotation/stars4.png', $this->icons->stars('4'));
        $this->assertNull($this->icons->stars('6'));
        $this->assertNull($this->icons->stars(''));
    }

    public function testContainers(): void
    {
        $this->assertSame('/img/container/small.gif', $this->icons->container('small'));
        $this->assertSame('/img/container/not_chosen.gif', $this->icons->container('not chosen'));
        $this->assertNull($this->icons->container('unknown'));
        $this->assertNull($this->icons->container(''));
    }

    public function testAttributes(): void
    {
        $this->assertSame('/img/attributes/dogs-yes.gif', $this->icons->attribute(1, true));
        $this->assertSame('/img/attributes/dogs-no.gif', $this->icons->attribute(1, false));
        $this->assertNull($this->icons->attribute(2, false), 'fee has no negative variant');
        $this->assertNull($this->icons->attribute(9999, true));
    }

    public function testLogTypes(): void
    {
        $this->assertSame('/img/log/icon_smile.png', $this->icons->logType('Found it'));
        $this->assertSame('/img/log/icon_sad.png', $this->icons->logType("Didn't find it"));
        $this->assertNull($this->icons->logType('Some Future Log Type'));
    }

    public function testLocaleTexts(): void
    {
        $this->assertSame('Difficulty:', $this->locales->text('en', 'difficulty'));
        $this->assertSame('Difficulté :', $this->locales->text('fr', 'difficulty'));
        $this->assertSame('Waypoints', $this->locales->text('en', 'waypoints'));
        $this->assertSame('Spoilers', $this->locales->text('en', 'spoilers'));
        $this->assertSame('missing_id', $this->locales->text('en', 'missing_id'));
    }

    public function testDateFormats(): void
    {
        $date = new \DateTimeImmutable('2026-01-15');
        $this->assertSame('2026/01/15', $this->locales->formatDate('en', $date));
        $this->assertSame('15/01/2026', $this->locales->formatDate('fr', $date));
    }

    public function testUnknownLocaleThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->locales->text('xx', 'difficulty');
    }
}
