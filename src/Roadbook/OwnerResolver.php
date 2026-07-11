<?php

namespace App\Roadbook;

use Geocaching\GeocachingSdk;

/**
 * Resolves geocache owners (current usernames) from the Geocaching Live API.
 *
 * The batch endpoint (GET /v1/geocaches) silently omits caches the caller
 * cannot see — notably unpublished ones. Codes missing from a *successful*
 * batch response are retried individually: GET /v1/geocaches/{code} does
 * return an unpublished cache to its owner. If the batch call itself throws
 * (API down/timeout), that chunk's codes are skipped entirely rather than
 * retried one by one, to avoid up to BATCH_SIZE-many sequential failing
 * calls per chunk. Every failure is swallowed so the roadbook falls back
 * to the GPX placed_by value.
 */
final class OwnerResolver
{
    private const int BATCH_SIZE = 50;

    /**
     * @param list<string> $referenceCodes
     *
     * @return array<string, string> reference code => owner username
     */
    public function resolve(GeocachingSdk $sdk, array $referenceCodes): array
    {
        $owners          = [];
        $retryCandidates = [];

        foreach (array_chunk($referenceCodes, self::BATCH_SIZE) as $chunk) {
            try {
                $response = $sdk->getGeocaches([
                    'referenceCodes' => implode(',', $chunk),
                    'fields'         => 'referenceCode,owner',
                    'lite'           => 'true',
                ]);
                /** @var list<array{referenceCode?: string, owner?: array{username?: string}}> $geocaches */
                $geocaches = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                foreach ($geocaches as $geocache) {
                    if (isset($geocache['referenceCode'], $geocache['owner']['username'])) {
                        $owners[$geocache['referenceCode']] = $geocache['owner']['username'];
                    }
                }
                // Batch call succeeded — codes it omitted are eligible for
                // an individual retry (e.g. unpublished caches).
                array_push($retryCandidates, ...$chunk);
            } catch (\Throwable) {
                // Batch call itself failed — skip individual retries for
                // this chunk's codes to avoid many sequential failing calls.
            }
        }

        foreach ($retryCandidates as $code) {
            if (isset($owners[$code])) {
                continue;
            }
            try {
                $response = $sdk->getGeocache($code, ['fields' => 'referenceCode,owner']);
                /** @var array{referenceCode?: string, owner?: array{username?: string}} $geocache */
                $geocache = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                if (isset($geocache['owner']['username'])) {
                    $owners[$code] = $geocache['owner']['username'];
                }
            } catch (\Throwable) {
                // Not visible to this user (unpublished cache of someone else,
                // archived+locked, ...) — keep the GPX placed_by fallback.
            }
        }

        return $owners;
    }
}
