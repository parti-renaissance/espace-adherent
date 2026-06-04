<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Thin client over the external indexer (call-indexer.txt). Single items go to POST /index, batches
 * to POST /index/batch as a bare JSON array. The Content-Type is set by the `json` option.
 *
 * The base URL is optional: when TIMELINE_INDEXER_URL is empty the indexer is considered disabled
 * and every call is a logged no-op (the mirror still works, only the push is skipped). This is why
 * the request targets the full URL on the default HttpClient rather than a scoped client — a scoped
 * client validates its base_uri at construction and would fail to boot dev/test/CI (and prod before
 * the secret is set).
 *
 * Retry policy: 5xx and 429 throw so Messenger retries; other 4xx are logged and swallowed
 * (non-retryable — e.g. a 422 must never loop).
 */
class IndexerClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $timelineIndexerBaseUrl,
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
        $baseUrl = rtrim(trim($this->timelineIndexerBaseUrl), '/');

        if ('' === $baseUrl) {
            $this->logger->debug('Timeline indexer URL not configured; push skipped.', ['path' => $path]);

            return;
        }

        $response = $this->httpClient->request('POST', $baseUrl.$path, [
            'headers' => ['Accept' => 'application/json'],
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
