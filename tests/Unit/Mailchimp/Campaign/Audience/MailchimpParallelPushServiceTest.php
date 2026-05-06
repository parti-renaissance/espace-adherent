<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience;

use App\Mailchimp\Campaign\Audience\MailchimpParallelPushService;
use App\Mailchimp\Driver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MailchimpParallelPushServiceTest extends TestCase
{
    public function testPushEmailsWithEmptyEmailsReturnsZeroResultWithoutHttpCall(): void
    {
        $client = new MockHttpClient([]);
        $service = new MailchimpParallelPushService(new Driver($client, 'list-abc'));

        $result = $service->pushEmails(123, 'list-abc', []);

        self::assertSame(0, $result->okCount);
        self::assertSame(0, $result->erroredCount);
        self::assertSame([], $result->refusedEmails);
        self::assertSame(0, $client->getRequestsCount());
    }

    public function testPushEmailsWith1250EmailsProducesThreeChunks(): void
    {
        $emails = $this->generateEmails(1250);
        $responses = [
            new MockResponse(json_encode(['total_added' => 500]), ['http_code' => 200]),
            new MockResponse(json_encode(['total_added' => 500]), ['http_code' => 200]),
            new MockResponse(json_encode(['total_added' => 250]), ['http_code' => 200]),
        ];
        $client = new MockHttpClient($responses, 'https://us16.api.mailchimp.com');
        $service = new MailchimpParallelPushService(new Driver($client, 'list-abc'));

        $result = $service->pushEmails(99, 'list-abc', $emails, concurrency: 5);

        self::assertSame(3, $result->okCount);
        self::assertSame(0, $result->erroredCount);
        self::assertTrue($result->isSuccess());
        self::assertSame(3, $client->getRequestsCount());
    }

    public function testPushEmailsWithPartialRefusalsCollectsRefusedEmails(): void
    {
        $emails = ['a@b.com', 'invalid@x.com', 'c@d.fr'];
        $body = json_encode([
            'total_added' => 2,
            'errors' => [
                ['email_address' => 'invalid@x.com', 'error_code' => 'ERROR_CONTACT_EXISTS'],
            ],
        ]);
        $client = new MockHttpClient([new MockResponse($body, ['http_code' => 200])], 'https://us16.api.mailchimp.com');
        $service = new MailchimpParallelPushService(new Driver($client, 'list-abc'));

        $result = $service->pushEmails(99, 'list-abc', $emails);

        self::assertSame(1, $result->okCount);
        self::assertSame(0, $result->erroredCount);
        self::assertSame(['invalid@x.com'], $result->refusedEmails);
    }

    public function testPushEmailsWithRateLimitRetriesUntilSuccess(): void
    {
        $emails = ['a@b.com'];
        $responses = [
            new MockResponse('Too Many', ['http_code' => 429]),
            new MockResponse('Too Many', ['http_code' => 429]),
            new MockResponse(json_encode(['total_added' => 1]), ['http_code' => 200]),
        ];
        $client = new MockHttpClient($responses, 'https://us16.api.mailchimp.com');
        $service = new TestableMailchimpParallelPushService(new Driver($client, 'list-abc'));

        $result = $service->pushEmails(99, 'list-abc', $emails, concurrency: 1);

        self::assertSame(1, $result->okCount);
        self::assertSame(0, $result->erroredCount);
        self::assertSame(3, $client->getRequestsCount(), '2 retries + 1 success');
    }

    public function testPushEmailsWhenAllRetriesFailRecordsAsErrored(): void
    {
        $emails = ['a@b.com'];
        $responses = [
            new MockResponse('Too Many', ['http_code' => 429]),
            new MockResponse('Too Many', ['http_code' => 429]),
            new MockResponse('Too Many', ['http_code' => 429]),
            new MockResponse('Too Many', ['http_code' => 429]),
        ];
        $client = new MockHttpClient($responses, 'https://us16.api.mailchimp.com');
        $service = new TestableMailchimpParallelPushService(new Driver($client, 'list-abc'));

        $result = $service->pushEmails(99, 'list-abc', $emails, concurrency: 1);

        self::assertSame(0, $result->okCount);
        self::assertSame(1, $result->erroredCount);
        self::assertFalse($result->isSuccess());
        self::assertCount(1, $result->errorMessages);
        self::assertStringContainsString('HTTP 429', $result->errorMessages[0]);
    }

    public function testPushEmailsWith400ErrorRecordsErrorWithoutRetry(): void
    {
        $emails = ['a@b.com'];
        $responses = [
            new MockResponse('Bad Request', ['http_code' => 400]),
        ];
        $client = new MockHttpClient($responses, 'https://us16.api.mailchimp.com');
        $service = new MailchimpParallelPushService(new Driver($client, 'list-abc'));

        $result = $service->pushEmails(99, 'list-abc', $emails);

        self::assertSame(0, $result->okCount);
        self::assertSame(1, $result->erroredCount);
        self::assertSame(1, $client->getRequestsCount(), 'no retry on 4xx other than 429');
    }

    public function testPushEmailsOnChunkSuccessCallbackCalledOncePerOkChunk(): void
    {
        $emails = $this->generateEmails(1500); // 3 chunks
        $responses = [
            new MockResponse(json_encode(['total_added' => 500]), ['http_code' => 200]),
            new MockResponse(json_encode(['total_added' => 500]), ['http_code' => 200]),
            new MockResponse(json_encode(['total_added' => 500]), ['http_code' => 200]),
        ];
        $client = new MockHttpClient($responses, 'https://us16.api.mailchimp.com');
        $service = new MailchimpParallelPushService(new Driver($client, 'list-abc'));

        $callbackCount = 0;
        $service->pushEmails(99, 'list-abc', $emails, onChunkSuccess: static function () use (&$callbackCount) {
            ++$callbackCount;
        });

        self::assertSame(3, $callbackCount);
    }

    public function testPushEmailsCancellationProbeReturnsTrueStopsDispatchingAndReturnsPartial(): void
    {
        $emails = $this->generateEmails(2500); // 5 chunks
        // We never get to send all 5 because cancellation triggers after the first
        $responses = array_fill(0, 5, new MockResponse(json_encode(['total_added' => 500]), ['http_code' => 200]));
        $client = new MockHttpClient($responses, 'https://us16.api.mailchimp.com');
        $service = new MailchimpParallelPushService(new Driver($client, 'list-abc'));

        $callCount = 0;
        $probe = static function () use (&$callCount): bool {
            return ++$callCount > 1; // returns true on 2nd call (after first dispatch)
        };

        $result = $service->pushEmails(99, 'list-abc', $emails, concurrency: 1, cancellationProbe: $probe);

        self::assertLessThan(5, $client->getRequestsCount(), 'cancellation must short-circuit the queue');
        self::assertContains('Push cancelled cooperatively.', $result->errorMessages);
    }

    public function testPushEmailsDispatchedRequestsUseCorrectUrlAndPayload(): void
    {
        $emails = ['a@b.com', 'c@d.fr'];
        $captured = [];
        $client = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url, 'body' => $options['body'] ?? null];

            return new MockResponse(json_encode(['total_added' => 2]), ['http_code' => 200]);
        });
        $service = new MailchimpParallelPushService(new Driver($client, 'list-abc'));

        $service->pushEmails(456, 'my-list', $emails);

        self::assertCount(1, $captured);
        self::assertSame('POST', $captured[0]['method']);
        self::assertStringContainsString('/3.0/lists/my-list/segments/456', $captured[0]['url']);
        self::assertJsonStringEqualsJsonString(
            json_encode(['members_to_add' => ['a@b.com', 'c@d.fr']]),
            (string) $captured[0]['body'],
        );
    }

    /**
     * @return list<string>
     */
    private function generateEmails(int $count): array
    {
        $emails = [];
        for ($i = 0; $i < $count; ++$i) {
            $emails[] = "user{$i}@example.com";
        }

        return $emails;
    }
}

/**
 * Subclass for tests that involve 429 backoff — short-circuits the actual sleep
 * to avoid slowing down the test suite.
 */
class TestableMailchimpParallelPushService extends MailchimpParallelPushService
{
    protected function sleep(float $seconds): void
    {
        // No-op in tests
    }
}
