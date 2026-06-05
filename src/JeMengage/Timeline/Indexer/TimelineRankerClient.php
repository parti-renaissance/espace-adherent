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
 * A transport error, a non-2xx status or an invalid payload surfaces as a RuntimeException; for a canary
 * GetTimelineFeedsController catches it and falls back to the regular Algolia feed. An empty/malformed
 * TIMELINE_RANKER_URL is instead a configuration error: the scoped client fails to build (a 500) before the
 * request is even made, so it is not caught by that fallback.
 */
class TimelineRankerClient
{
    public function __construct(private readonly HttpClientInterface $timelineRankerClient)
    {
    }

    public function getItems(UserProfile $profile, ?string $sessionId = null): FeedResponse
    {
        $body = $profile->jsonSerialize();
        if (null !== $sessionId) {
            $body['session_id'] = $sessionId;
        }

        $response = $this->timelineRankerClient->request('POST', '/get_items', [
            'json' => $body,
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new \RuntimeException(\sprintf('Ranker get_items failed with status %d.', $response->getStatusCode()));
        }

        try {
            $data = $response->toArray(false);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Ranker get_items returned an invalid JSON payload.', 0, $exception);
        }

        return FeedResponse::fromArray($data);
    }
}
