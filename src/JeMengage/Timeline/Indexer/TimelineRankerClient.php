<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Thin client over the external timeline ranker (POST /get_items) — a different host from the indexer's
 * write endpoints. The base URL, timeout and Accept header live in the client config: the framework
 * scoped client timeline_ranker.client (config/packages/http_client.php), autowired here by argument name
 * ($timelineRankerClient). TIMELINE_RANKER_URL must be an origin (scheme + host): /get_items is an
 * absolute-path reference, so any path in the base URL would be replaced (RFC 3986).
 *
 * Unlike the push, the read has no silent no-op — a canary requires the ranker. A transport error, a non-2xx
 * status or an invalid payload surfaces as a RuntimeException, which GetTimelineFeedsController maps to a 503.
 * An empty/malformed TIMELINE_RANKER_URL is a configuration error: the scoped client fails to build (a 500),
 * not a per-request 503.
 */
class TimelineRankerClient
{
    public function __construct(private readonly HttpClientInterface $timelineRankerClient)
    {
    }

    public function getItems(UserProfile $profile): FeedResponse
    {
        $response = $this->timelineRankerClient->request('POST', '/get_items', [
            'json' => $profile,
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new \RuntimeException(\sprintf('Ranker get_items failed with status %d.', $response->getStatusCode()));
        }

        return FeedResponse::fromArray($response->toArray(false));
    }
}
