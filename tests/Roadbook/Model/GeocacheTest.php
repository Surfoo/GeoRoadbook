<?php

namespace App\Tests\Roadbook\Model;

use App\Roadbook\Model\Geocache;
use PHPUnit\Framework\TestCase;

final class GeocacheTest extends TestCase
{
    private function makeCache(): Geocache
    {
        return new Geocache(
            code: 'GC12345',
            name: 'Test cache',
            type: 'Traditional Cache',
            latitude: 48.85,
            longitude: 2.35,
            displayCoordinates: 'N 48° 51.000 E 002° 21.000',
            difficulty: '1.5',
            terrain: '2',
            container: 'Small',
            placedBy: 'GpxOwnerName',
            hiddenDate: null,
            attributes: [],
            description: null,
            descriptionIsHtml: false,
            hint: null,
            logs: [],
            waypoints: [],
            spoilers: [],
        );
    }

    public function testOwnerDefaultsToNull(): void
    {
        $this->assertNull($this->makeCache()->owner);
    }

    public function testWithOwnerReturnsNewInstanceWithOwnerSet(): void
    {
        $cache    = $this->makeCache();
        $enriched = $cache->withOwner('ApiOwnerName');

        $this->assertSame('ApiOwnerName', $enriched->owner);
        $this->assertSame('GpxOwnerName', $enriched->placedBy);
        $this->assertNull($cache->owner); // original untouched
    }
}
