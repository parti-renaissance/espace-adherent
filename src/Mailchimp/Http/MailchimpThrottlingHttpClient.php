<?php

declare(strict_types=1);

namespace App\Mailchimp\Http;

use App\Mailchimp\Concurrency\MailchimpPriorityContext;
use App\Mailchimp\Concurrency\MailchimpSemaphore;
use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MailchimpThrottlingHttpClient implements HttpClientInterface
{
    use AsyncDecoratorTrait;

    public function __construct(
        HttpClientInterface $client,
        private readonly MailchimpSemaphore $semaphore,
        private readonly MailchimpPriorityContext $priorityContext,
    ) {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // A parent middleware may already hold the slot for the whole handler.
        // In that case, skip the per-request acquire/release: the call piggy-backs
        // on the parent's slot and Mailchimp concurrency stays bounded.
        if ($this->priorityContext->hasHeldSlot()) {
            return $this->client->request($method, $url, $options);
        }

        $slot = $this->semaphore->acquire($this->priorityContext->getPriority());

        return new AsyncResponse(
            $this->client,
            $method,
            $url,
            $options,
            static function (ChunkInterface $chunk, AsyncContext $context) use ($slot): \Generator {
                try {
                    $finished = $chunk->isLast() || $chunk->isTimeout();
                } catch (\Throwable) {
                    // ErrorChunk: isLast() rethrows the transport error. The request is finished either way.
                    $finished = true;
                }
                if ($finished) {
                    $slot->release();
                }
                yield $chunk;
            },
        );
    }
}
