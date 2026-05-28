<?php

declare(strict_types=1);

namespace Tests\App\Chatbot\Http;

use App\Chatbot\Http\VertexAiSseHttpClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[Group('chatbot')]
class VertexAiSseHttpClientTest extends TestCase
{
    public function testStreamGenerateContentUrlReceivesAltSseQuery(): void
    {
        $url = 'https://aiplatform.googleapis.com/v1/projects/x/locations/europe-west1/publishers/google/models/gemini-3-flash-preview:streamGenerateContent';
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createStub(ResponseInterface::class);

        $inner->expects(self::once())
            ->method('request')
            ->with('POST', $url, self::callback(static function (array $options): bool {
                return ['alt' => 'sse'] === ($options['query'] ?? null)
                    && 'value' === ($options['json']['foo'] ?? null);
            }))
            ->willReturn($response);

        $client = new VertexAiSseHttpClient($inner);

        self::assertSame($response, $client->request('POST', $url, ['json' => ['foo' => 'value']]));
    }

    public function testStreamGenerateContentMergesExistingQuery(): void
    {
        $url = 'https://aiplatform.googleapis.com/v1/publishers/google/models/x:streamGenerateContent';
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createStub(ResponseInterface::class);

        $inner->expects(self::once())
            ->method('request')
            ->with('POST', $url, self::callback(static function (array $options): bool {
                return ['key' => 'secret', 'alt' => 'sse'] === ($options['query'] ?? null);
            }))
            ->willReturn($response);

        new VertexAiSseHttpClient($inner)->request('POST', $url, ['query' => ['key' => 'secret']]);
    }

    public function testNonStreamingUrlIsLeftUntouched(): void
    {
        $url = 'https://aiplatform.googleapis.com/v1/publishers/google/models/x:generateContent';
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createStub(ResponseInterface::class);

        $inner->expects(self::once())
            ->method('request')
            ->with('POST', $url, ['json' => ['foo' => 'value']])
            ->willReturn($response);

        new VertexAiSseHttpClient($inner)->request('POST', $url, ['json' => ['foo' => 'value']]);
    }
}
