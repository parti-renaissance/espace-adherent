<?php

declare(strict_types=1);

namespace App\Mailchimp\Http;

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
    ) {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $slot = $this->semaphore->acquire();

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
