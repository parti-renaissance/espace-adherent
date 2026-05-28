<?php

declare(strict_types=1);

namespace App\Chatbot\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * Forces "alt=sse" on Vertex AI streamGenerateContent requests so the API responds
 * with a proper text/event-stream instead of a chunked JSON array. Without this,
 * Symfony AI's SseStream parser falls back to a fragile chunk-splitting heuristic
 * that throws JSON_ERROR_CTRL_CHAR when an HTTP chunk cuts inside a string value.
 */
class VertexAiSseHttpClient implements HttpClientInterface
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if (str_contains($url, ':streamGenerateContent')) {
            $options['query'] = array_merge($options['query'] ?? [], ['alt' => 'sse']);
        }

        return $this->client->request($method, $url, $options);
    }

    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        return new self($this->client->withOptions($options));
    }
}
