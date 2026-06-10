<?php

declare(strict_types=1);

namespace Tests\App\Chatbot\Platform;

use App\Chatbot\Platform\UsageCapturingResultConverter;
use App\Chatbot\Usage\ChatbotUsageTracker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Exception\AuthenticationException;
use Symfony\AI\Platform\Result\RawHttpResult;
use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\AI\Platform\TokenUsage\TokenUsage;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[Group('functional')]
#[Group('chatbot')]
class UsageCapturingResultConverterTest extends TestCase
{
    private const string ANSWER = "Les éléments d'information sur ce point précis ne sont pas recensés dans notre base actuelle. Nous faisons remonter votre interrogation à nos équipes pour analyse.";

    private const array USAGE = [
        'prompt_tokens' => 9200,
        'completion_tokens' => 420,
        'total_tokens' => 9620,
        'prompt_tokens_details' => [
            'cached_tokens' => 7014,
        ],
        'by_model' => [
            'model-a' => [
                'input_tokens' => 1292,
                'output_tokens' => 269,
                'cache_read_tokens' => 7014,
                'cache_creation_tokens' => 336,
            ],
            'model-b' => [
                'input_tokens' => 558,
                'output_tokens' => 151,
                'cache_read_tokens' => 0,
                'cache_creation_tokens' => 0,
            ],
        ],
        'cost_usd' => 0.012588,
    ];

    public function testCapturesUsageFromCompletionResponse(): void
    {
        $tracker = $this->createMock(ChatbotUsageTracker::class);
        $tracker->expects(self::once())->method('capture')->with(self::USAGE);

        $converter = new UsageCapturingResultConverter($tracker);
        $result = $converter->convert($this->createRagHttpResult([
            'id' => 'chatcmpl-c911b0cf591c4bd99c0789ec',
            'object' => 'chat.completion',
            'created' => 1781012105,
            'model' => 'antiseche-rag',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => self::ANSWER,
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => self::USAGE,
        ]));

        self::assertInstanceOf(TextResult::class, $result);
        self::assertSame(self::ANSWER, $result->getContent());
    }

    public function testCapturesUsageFromFinalStreamChunk(): void
    {
        $tracker = $this->createMock(ChatbotUsageTracker::class);
        $tracker->expects(self::once())->method('capture')->with(self::USAGE);

        $rawResult = $this->createStub(RawResultInterface::class);
        $rawResult->method('getObject')->willReturn(new class {
            public function getStatusCode(): int
            {
                return 200;
            }
        });
        $rawResult->method('getDataStream')->willReturn([
            ['choices' => [['index' => 0, 'delta' => ['content' => self::ANSWER]]]],
            ['choices' => [['index' => 0, 'delta' => [], 'finish_reason' => 'stop']]],
            ['choices' => [], 'usage' => self::USAGE],
        ]);

        $converter = new UsageCapturingResultConverter($tracker);
        $deltas = iterator_to_array($converter->convert($rawResult, ['stream' => true])->getContent(), false);

        $texts = array_filter($deltas, static fn (object $delta) => $delta instanceof TextDelta);
        $usages = array_filter($deltas, static fn (object $delta) => $delta instanceof TokenUsage);

        self::assertCount(1, $texts);
        self::assertSame(self::ANSWER, reset($texts)->getText());
        self::assertCount(1, $usages);
    }

    public function testDoesNotCaptureUsageOnErrorResponse(): void
    {
        $tracker = $this->createMock(ChatbotUsageTracker::class);
        $tracker->expects(self::never())->method('capture');

        $converter = new UsageCapturingResultConverter($tracker);

        $this->expectException(AuthenticationException::class);

        $converter->convert($this->createRagHttpResult(['error' => ['message' => 'Invalid API key']], 401));
    }

    private function createRagHttpResult(array $payload, int $status = 200): RawHttpResult
    {
        $client = new MockHttpClient(new MockResponse(json_encode($payload, \JSON_THROW_ON_ERROR), [
            'http_code' => $status,
            'response_headers' => ['content-type' => 'application/json'],
        ]));

        return new RawHttpResult($client->request('POST', 'https://antiseche.test/v1/chat/completions'));
    }
}
