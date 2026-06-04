<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Thin client over the external indexer (call-indexer.txt). Single items go to POST /index, batches to
 * POST /index/batch as a bare JSON array. The base URL and Accept header live in the client config: the
 * framework scoped client timeline_indexer.client (config/packages/http_client.php), autowired here by
 * argument name ($timelineIndexerClient). TIMELINE_INDEXER_URL must be an origin (scheme + host): the
 * endpoints are absolute-path references (RFC 3986). The Content-Type is set by the `json` option.
 *
 * Retry policy: 5xx and 429 throw so Messenger retries; other 4xx are logged and swallowed
 * (non-retryable — e.g. a 422 must never loop).
 */
class IndexerClient
{
    public function __construct(
        private readonly HttpClientInterface $timelineIndexerClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function index(ItemPayload $payload): void
    {
        $this->send('/index', $payload->jsonSerialize());
    }

    /**
     * @param ItemPayload[] $payloads
     */
    public function indexBatch(array $payloads): void
    {
        if (!$payloads) {
            return;
        }

        $this->send('/index/batch', array_map(static fn (ItemPayload $payload) => $payload->jsonSerialize(), array_values($payloads)));
    }

    private function send(string $path, array $body): void
    {
        $response = $this->timelineIndexerClient->request('POST', $path, [
            'json' => $body,
        ]);

        $status = $response->getStatusCode();

        if ($status >= 500 || 429 === $status) {
            throw new \RuntimeException(\sprintf('Indexer push failed with retryable status %d on %s.', $status, $path));
        }

        if ($status >= 400) {
            $this->logger->error('Indexer push rejected (non-retryable).', ['path' => $path, 'status' => $status]);
        }
    }
}
