<?php

namespace App\Tests\Roadbook;

use App\Roadbook\OwnerResolver;
use Geocaching\GeocachingSdk;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class OwnerResolverTest extends TestCase
{
    public function testResolvesOwnersFromBatchEndpoint(): void
    {
        $sdk = $this->createMock(GeocachingSdk::class);
        $sdk->expects($this->once())
            ->method('getGeocaches')
            ->with([
                'referenceCodes' => 'GC00001,GC00002',
                'fields'         => 'referenceCode,owner',
                'lite'           => 'true',
            ])
            ->willReturn(new Response(200, [], json_encode([
                ['referenceCode' => 'GC00001', 'owner' => ['username' => 'Alice']],
                ['referenceCode' => 'GC00002', 'owner' => ['username' => 'Bob']],
            ])));
        $sdk->expects($this->never())->method('getGeocache');

        $result = new OwnerResolver()->resolve($sdk, ['GC00001', 'GC00002']);

        $this->assertSame(['GC00001' => 'Alice', 'GC00002' => 'Bob'], $result);
    }

    public function testFallsBackToSingleFetchForCodesMissingFromBatch(): void
    {
        // GC99999 is unpublished: omitted by the batch endpoint,
        // but returned by getGeocache() because the caller owns it.
        $sdk = $this->createMock(GeocachingSdk::class);
        $sdk->method('getGeocaches')->willReturn(new Response(200, [], json_encode([
            ['referenceCode' => 'GC00001', 'owner' => ['username' => 'Alice']],
        ])));
        $sdk->expects($this->once())
            ->method('getGeocache')
            ->with('GC99999', ['fields' => 'referenceCode,owner'])
            ->willReturn(new Response(200, [], json_encode(
                ['referenceCode' => 'GC99999', 'owner' => ['username' => 'Surfoo']],
            )));

        $result = new OwnerResolver()->resolve($sdk, ['GC00001', 'GC99999']);

        $this->assertSame(['GC00001' => 'Alice', 'GC99999' => 'Surfoo'], $result);
    }

    public function testSingleFetchFailureIsSwallowed(): void
    {
        // Unpublished cache owned by someone else: API answers 403/404,
        // the SDK throws — the cache is simply left out of the map.
        $sdk = $this->createMock(GeocachingSdk::class);
        $sdk->method('getGeocaches')->willReturn(new Response(200, [], '[]'));
        $sdk->method('getGeocache')->willThrowException(new \RuntimeException('403 Forbidden'));

        $this->assertSame([], new OwnerResolver()->resolve($sdk, ['GC77777']));
    }

    public function testBatchesAreChunkedBy50(): void
    {
        $codes = array_map(fn (int $i) => sprintf('GC%05d', $i), range(1, 60));

        $sdk = $this->createMock(GeocachingSdk::class);
        $sdk->expects($this->exactly(2))
            ->method('getGeocaches')
            ->willReturnCallback(function (array $query): Response {
                $chunk = explode(',', (string) $query['referenceCodes']);
                $this->assertLessThanOrEqual(50, count($chunk));

                return new Response(200, [], json_encode(array_map(
                    fn (string $code) => ['referenceCode' => $code, 'owner' => ['username' => 'X']],
                    $chunk,
                )));
            });

        $result = new OwnerResolver()->resolve($sdk, $codes);

        $this->assertCount(60, $result);
    }

    public function testBatchFailureFallsBackToEmptyMapNotException(): void
    {
        $sdk = $this->createMock(GeocachingSdk::class);
        $sdk->method('getGeocaches')->willThrowException(new \RuntimeException('API down'));
        $sdk->method('getGeocache')->willThrowException(new \RuntimeException('API down'));

        $this->assertSame([], new OwnerResolver()->resolve($sdk, ['GC00001']));
    }
}
