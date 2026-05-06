<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

use App\Mailchimp\Driver;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MailchimpParallelPushService
{
    private const int CHUNK_SIZE = 500;
    private const int MAX_RETRIES_429 = 3;

    private LoggerInterface $logger;

    public function __construct(
        private readonly Driver $driver,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Pushes emails into an existing Mailchimp static segment via N direct
     * parallel requests (not via /batches).
     *
     * @param list<string>     $emails            emails to push (assumed valid)
     * @param int              $concurrency       strict cap on in-flight requests
     * @param ?callable():bool $cancellationProbe called between dispatches — `true` = cooperative stop
     * @param ?callable():void $onChunkSuccess    called after each successful chunk (UI progress)
     */
    public function pushEmails(
        int $segmentId,
        string $listId,
        array $emails,
        int $concurrency = 5,
        ?callable $onChunkSuccess = null,
        ?callable $cancellationProbe = null,
    ): PushResult {
        $start = microtime(true);

        if (!$emails) {
            return new PushResult(0, 0, 0, 0, [], [], 0.0);
        }

        $uri = \sprintf('/lists/%s/segments/%d', $listId, $segmentId);

        // Queue : list of [emailsChunk, retryCount]
        $queue = [];
        foreach (array_chunk($emails, self::CHUNK_SIZE) as $chunk) {
            $queue[] = [$chunk, 0];
        }

        /** @var array<int, array{response: ResponseInterface, chunk: list<string>, retry: int}> $pending */
        $pending = [];

        $okCount = 0;
        $erroredCount = 0;
        $addedCount = 0;
        $refusedCount = 0;
        $refusedEmails = [];
        $errorMessages = [];
        $cancelled = false;

        while ($queue || $pending) {
            // Cancellation probe between dispatches
            if (!$cancelled && null !== $cancellationProbe && $cancellationProbe()) {
                $cancelled = true;
                foreach ($pending as $entry) {
                    $entry['response']->cancel();
                }
                $pending = [];
                $queue = [];
                break;
            }

            // Fill the pipe up to concurrency
            while (\count($pending) < $concurrency && $queue) {
                [$chunk, $retry] = array_shift($queue);
                $response = $this->dispatchChunk($uri, $chunk);
                if (null === $response) {
                    ++$erroredCount;
                    $errorMessages[] = 'HTTP transport error on chunk dispatch';
                    continue;
                }
                $pending[spl_object_id($response)] = [
                    'response' => $response,
                    'chunk' => $chunk,
                    'retry' => $retry,
                ];
            }

            if (!$pending) {
                break;
            }

            $responses = array_column($pending, 'response');

            $processed = $this->processNextCompletedResponse($responses, $pending);
            if (null === $processed) {
                continue;
            }

            [$response, $entry] = $processed;
            $outcome = $this->handleResponse($response, $entry['chunk'], $entry['retry']);

            if ('ok' === $outcome['status']) {
                ++$okCount;
                $addedCount += $outcome['addedCount'];
                $refusedCount += $outcome['refusedCount'];
                $refusedEmails = [...$refusedEmails, ...$outcome['refused']];
                if ($onChunkSuccess) {
                    $onChunkSuccess();
                }
            } elseif ('retry' === $outcome['status']) {
                // Synchronous backoff before re-enqueuing.
                // Acceptable in V1 — Mailchimp 429 means we're rate-limited anyway.
                $this->sleep($outcome['delaySeconds']);
                $queue[] = [$entry['chunk'], $entry['retry'] + 1];
            } else { // 'error'
                ++$erroredCount;
                $refusedCount += \count($entry['chunk']);
                $errorMessages[] = $outcome['message'];
            }
        }

        $duration = microtime(true) - $start;

        if ($cancelled) {
            $errorMessages[] = 'Push cancelled cooperatively.';
        }

        return new PushResult($okCount, $erroredCount, $addedCount, $refusedCount, $refusedEmails, $errorMessages, $duration);
    }

    /**
     * @param list<string> $chunk
     */
    private function dispatchChunk(string $uri, array $chunk): ?ResponseInterface
    {
        return $this->driver->send('POST', $uri, ['members_to_add' => $chunk], blockOnResponseLog: false);
    }

    /**
     * @param list<string> $chunk
     *
     * @return array{status: 'ok', refused: list<string>, addedCount: int, refusedCount: int}
     *                                                                                        |array{status: 'retry', delaySeconds: float}
     *                                                                                        |array{status: 'error', message: string}
     */
    private function handleResponse(ResponseInterface $response, array $chunk, int $retry): array
    {
        try {
            $statusCode = $response->getStatusCode();
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'HTTP transport: '.$e->getMessage()];
        }

        if (200 === $statusCode || 204 === $statusCode) {
            $data = [];
            try {
                $data = $response->toArray(throw: false);
            } catch (\Throwable) {
                // body unparsable, fall through to ok with no refusal info
            }

            $refused = $this->extractRefusedEmails($response);
            $totalAdded = isset($data['total_added']) ? (int) $data['total_added'] : null;
            $errorCount = isset($data['error_count']) ? (int) $data['error_count'] : null;

            // Mailchimp may answer 200 with total_added=0 when every email is rejected
            // (e.g. emails not subscribed to the parent list). Treat as error so the
            // failure surfaces in errored_count / errorSummary instead of being silent.
            if (null !== $totalAdded && 0 === $totalAdded && \count($chunk) > 0) {
                $sample = \array_slice($refused, 0, 3);
                $message = \sprintf(
                    'HTTP %d but total_added=0 on chunk of %d (error_count=%s)%s',
                    $statusCode,
                    \count($chunk),
                    null === $errorCount ? 'n/a' : (string) $errorCount,
                    $sample ? ' — sample refused: '.implode(', ', $sample) : '',
                );
                $this->logger->warning('Mailchimp push chunk added 0 emails', [
                    'total_added' => 0,
                    'error_count' => $errorCount,
                    'chunk_size' => \count($chunk),
                ]);

                return ['status' => 'error', 'message' => $message];
            }

            // Default to chunk size when Mailchimp body is missing total_added (older variant or unparsable body).
            $addedCount = $totalAdded ?? \count($chunk);
            $refusedCount = $errorCount ?? \count($refused);

            return ['status' => 'ok', 'refused' => $refused, 'addedCount' => $addedCount, 'refusedCount' => $refusedCount];
        }

        if (429 === $statusCode && $retry < self::MAX_RETRIES_429) {
            // Exponential backoff: 2s → 4s → 8s
            $delay = 2.0 ** ($retry + 1);
            $this->logger->info('Mailchimp 429 — backoff retry', ['delay_s' => $delay, 'attempt' => $retry + 1]);

            return ['status' => 'retry', 'delaySeconds' => $delay];
        }

        $body = '';
        try {
            $body = $response->getContent(throw: false);
        } catch (\Throwable) {
            // ignore
        }

        $message = \sprintf('HTTP %d on chunk of %d emails: %s', $statusCode, \count($chunk), substr($body, 0, 500));
        $this->logger->warning('Mailchimp push chunk failed', ['status' => $statusCode, 'body_excerpt' => substr($body, 0, 500)]);

        return ['status' => 'error', 'message' => $message];
    }

    /**
     * Picks the first completed response from the stream and removes it from $pending.
     *
     * The foreach over $driver->stream() can throw from the iterator itself
     * (HttpExceptionInterface for 4xx/5xx). We catch the first completed response
     * and return immediately so the caller can handle it via handleResponse
     * (which supports 4xx/5xx through getStatusCode).
     *
     * @param list<ResponseInterface>                                                         $responses
     * @param array<int, array{response: ResponseInterface, chunk: list<string>, retry: int}> $pending
     *
     * @return array{0: ResponseInterface, 1: array{response: ResponseInterface, chunk: list<string>, retry: int}}|null
     */
    private function processNextCompletedResponse(array $responses, array &$pending): ?array
    {
        try {
            foreach ($this->driver->stream($responses) as $response => $streamChunk) {
                try {
                    if (!$streamChunk->isLast()) {
                        continue;
                    }
                } catch (\Throwable) {
                    // 4xx/5xx — the response is complete, return it for handling
                }

                $key = spl_object_id($response);
                if (!isset($pending[$key])) {
                    continue;
                }
                $entry = $pending[$key];
                unset($pending[$key]);

                return [$response, $entry];
            }
        } catch (\Throwable $e) {
            // The stream() generator may throw before we receive the chunk
            // (typically 4xx/5xx surfaced via current()). We identify the
            // offending response via $e->getResponse() when available.
            if (method_exists($e, 'getResponse')) {
                /** @var ResponseInterface $response */
                $response = $e->getResponse();
                $key = spl_object_id($response);
                if (isset($pending[$key])) {
                    $entry = $pending[$key];
                    unset($pending[$key]);

                    return [$response, $entry];
                }
            }
            // Fallback: we cannot identify the response, so re-throw rather
            // than swallow the error silently.
            throw $e;
        }

        return null;
    }

    /**
     * Test seam: lets tests short-circuit the actual sleep call.
     */
    protected function sleep(float $seconds): void
    {
        usleep((int) ($seconds * 1_000_000));
    }

    /**
     * @return list<string>
     */
    private function extractRefusedEmails(ResponseInterface $response): array
    {
        try {
            $data = $response->toArray(throw: false);
        } catch (\Throwable) {
            return [];
        }

        $errors = $data['errors'] ?? [];
        if (!\is_array($errors)) {
            return [];
        }

        $refused = [];
        foreach ($errors as $error) {
            if (\is_array($error) && isset($error['email_address'])) {
                $refused[] = (string) $error['email_address'];
            }
        }

        return $refused;
    }
}
