<?php

namespace App\Tests\Roadbook;

use App\Roadbook\GpxParser;
use App\Roadbook\Model\Geocache;
use PHPUnit\Framework\TestCase;

class GpxParserTest extends TestCase
{
    private GpxParser $parser;

    protected function setUp(): void
    {
        $this->parser = new GpxParser();
    }

    private function parseFixture(): array
    {
        return $this->parser->parse((string) file_get_contents(__DIR__ . '/fixtures/sample.gpx'));
    }

    public function testParsesSampleCache(): void
    {
        $caches = $this->parseFixture();

        $this->assertCount(1, $caches);
        $cache = $caches[0];

        $this->assertSame('GC1TEST', $cache->code);
        $this->assertSame('Tour Eiffel Cache', $cache->name);
        $this->assertSame('Traditional Cache', $cache->type);
        $this->assertSame('2', $cache->difficulty);
        $this->assertSame('1.5', $cache->terrain);
        $this->assertSame('small', $cache->container);
        $this->assertSame('TestOwner', $cache->placedBy);
        $this->assertSame('2026-01-15', $cache->hiddenDate?->format('Y-m-d'));
        $this->assertSame([['id' => 1, 'inc' => true]], $cache->attributes);
        $this->assertTrue($cache->descriptionIsHtml);
        $this->assertStringContainsString('hidden', (string) $cache->description);
        $this->assertSame('Under the bench', $cache->hint);
        $this->assertCount(1, $cache->logs);
        $this->assertSame('Found it', $cache->logs[0]->type);
        $this->assertSame('HappyFinder', $cache->logs[0]->finder);
        $this->assertSame('Nice one, TFTC!', $cache->logs[0]->text);
    }

    public function testCoordinateFormattingMatchesLegacyOutput(): void
    {
        $this->assertSame(
            'N 48° 51.502 E 002° 17.669',
            GpxParser::formatCoordinates(48.858370, 2.294481),
        );
        $this->assertSame(
            'S 33° 51.421 W 151° 12.876',
            GpxParser::formatCoordinates(-33.857016, -151.214606),
        );
    }

    public function testEquatorAndGreenwichHemispheres(): void
    {
        // The legacy XSLT produced an empty hemisphere for 0.x latitudes and W for 0.x longitudes
        $this->assertSame('N 00° 07.407 E 000° 07.407', GpxParser::formatCoordinates(0.123456, 0.123456));
    }

    public function testSkipsWaypointsWithoutCacheElement(): void
    {
        $gpx = <<<'XML'
            <?xml version="1.0" encoding="utf-8"?>
            <gpx xmlns="http://www.topografix.com/GPX/1/0" version="1.0" creator="test">
              <wpt lat="48.0" lon="2.0"><name>GC1PARK</name><sym>Parking Area</sym></wpt>
            </gpx>
            XML;

        $this->assertSame([], $this->parser->parse($gpx));
    }

    public function testRejectsInvalidXml(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->parser->parse('not xml at all');
    }

    public function testExtractsAdditionalWaypoints(): void
    {
        $caches = $this->parser->parse($this->gpxWithDescription(
            'Intro text<p>Additional Waypoints</p>'
            . 'PK1TEST - Parking GC1TEST<br />N 48° 51.000 E 002° 17.000<br />Park here<br />',
        ));

        $this->assertCount(1, $caches[0]->waypoints);
        $wpt = $caches[0]->waypoints[0];
        $this->assertStringContainsString('Parking', $wpt->title);
        $this->assertStringNotContainsString('GC1TEST', $wpt->title);
        $this->assertStringContainsString('N 48° 51.000', $wpt->coordinates);
        $this->assertSame('Park here', $wpt->comment);
    }

    public function testExtractsSpoilers(): void
    {
        $caches = $this->parser->parse($this->gpxWithDescription(
            'Text <!-- Spoiler4Gpx [The tree](https://example.org/spoiler.jpg) -->',
        ));

        $this->assertSame(
            [['title' => 'The tree', 'url' => 'https://example.org/spoiler.jpg']],
            $caches[0]->spoilers,
        );
    }

    public function testSortsByNameOwnerAndDifficulty(): void
    {
        $make = fn (string $name, string $owner, string $difficulty) => new Geocache(
            code: 'GC', name: $name, type: 'Traditional Cache',
            latitude: 0, longitude: 0, displayCoordinates: '',
            difficulty: $difficulty, terrain: '1', container: 'small',
            placedBy: $owner, hiddenDate: null, attributes: [],
            description: null, descriptionIsHtml: false, hint: null,
            logs: [], waypoints: [], spoilers: [],
        );

        $caches = [
            $make('Zebra', 'bob', '5'),
            $make('alpha', 'Alice', '1.5'),
            $make('Émile', 'carol', '2'),
        ];

        $byName = array_map(fn (Geocache $c) => $c->name, $this->parser->sort($caches, 'name'));
        $this->assertSame(['alpha', 'Émile', 'Zebra'], $byName, 'accent-aware, case-insensitive');

        $byOwner = array_map(fn (Geocache $c) => $c->placedBy, $this->parser->sort($caches, 'owner'));
        $this->assertSame(['Alice', 'bob', 'carol'], $byOwner);

        $byDifficulty = array_map(fn (Geocache $c) => $c->difficulty, $this->parser->sort($caches, 'difficulty'));
        $this->assertSame(['1.5', '2', '5'], $byDifficulty);

        $unsorted = array_map(fn (Geocache $c) => $c->name, $this->parser->sort($caches, 'none'));
        $this->assertSame(['Zebra', 'alpha', 'Émile'], $unsorted, 'document order preserved');
    }

    private function gpxWithDescription(string $description): string
    {
        $escaped = htmlspecialchars($description, ENT_XML1);

        return <<<XML
            <?xml version="1.0" encoding="utf-8"?>
            <gpx xmlns="http://www.topografix.com/GPX/1/0" version="1.0" creator="test">
              <wpt lat="48.0" lon="2.0">
                <time>2026-01-01T00:00:00Z</time>
                <name>GC1TEST</name>
                <groundspeak:cache xmlns:groundspeak="http://www.groundspeak.com/cache/1/0/1">
                  <groundspeak:name>Test</groundspeak:name>
                  <groundspeak:placed_by>Owner</groundspeak:placed_by>
                  <groundspeak:type>Traditional Cache</groundspeak:type>
                  <groundspeak:container>Small</groundspeak:container>
                  <groundspeak:difficulty>1</groundspeak:difficulty>
                  <groundspeak:terrain>1</groundspeak:terrain>
                  <groundspeak:long_description html="True">{$escaped}</groundspeak:long_description>
                </groundspeak:cache>
              </wpt>
            </gpx>
            XML;
    }
}
