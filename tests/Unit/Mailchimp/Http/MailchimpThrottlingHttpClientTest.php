<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Http;

use App\Mailchimp\Concurrency\Exception\MailchimpConcurrencyTimeoutException;
use App\Mailchimp\Concurrency\MailchimpSemaphore;
use App\Mailchimp\Concurrency\MailchimpSlot;
use App\Mailchimp\Http\MailchimpThrottlingHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MailchimpThrottlingHttpClientTest extends TestCase
{
    public function testRequestOnSuccessfulResponseAcquiresAndReleasesSlot(): void
    {
        $slot = new SpySlot();
        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore
            ->expects(self::once())
            ->method('acquire')
            ->willReturn($slot);

        $client = new MockHttpClient([new MockResponse('{"ok":true}', ['http_code' => 200])]);
        $throttling = new MailchimpThrottlingHttpClient($client, $semaphore);

        $response = $throttling->request('GET', 'https://api.mailchimp.test/3.0/lists');
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"ok":true}', $response->getContent());

        self::assertSame(1, $slot->releaseCount);
    }

    public function testRequestOnHttpErrorReleasesSlot(): void
    {
        $slot = new SpySlot();
        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore
            ->expects(self::once())
            ->method('acquire')
            ->willReturn($slot);

        $client = new MockHttpClient([new MockResponse('{"error":"boom"}', ['http_code' => 503])]);
        $throttling = new MailchimpThrottlingHttpClient($client, $semaphore);

        $response = $throttling->request('GET', 'https://api.mailchimp.test/3.0/lists');
        self::assertSame(503, $response->getStatusCode());
        self::assertSame('{"error":"boom"}', $response->getContent(false));

        self::assertSame(1, $slot->releaseCount);
    }

    public function testRequestOnTransportExceptionReleasesSlot(): void
    {
        $slot = new SpySlot();
        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore
            ->expects(self::once())
            ->method('acquire')
            ->willReturn($slot);

        $client = new MockHttpClient([new MockResponse('', ['error' => 'connection refused'])]);
        $throttling = new MailchimpThrottlingHttpClient($client, $semaphore);

        $response = $throttling->request('GET', 'https://api.mailchimp.test/3.0/lists');

        $caught = null;
        try {
            $response->getStatusCode();
        } catch (TransportException $e) {
            $caught = $e;
        }

        self::assertInstanceOf(TransportException::class, $caught);
        self::assertSame(1, $slot->releaseCount);
    }

    public function testRequestOnCancelReleasesSlot(): void
    {
        $slot = new SpySlot();
        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore
            ->expects(self::once())
            ->method('acquire')
            ->willReturn($slot);

        $client = new MockHttpClient([new MockResponse('{"ok":true}', ['http_code' => 200])]);
        $throttling = new MailchimpThrottlingHttpClient($client, $semaphore);

        $response = $throttling->request('GET', 'https://api.mailchimp.test/3.0/lists');
        $response->cancel();

        self::assertSame(1, $slot->releaseCount);
    }

    public function testStreamMultipleResponsesReleasesEachSlot(): void
    {
        $slot1 = new SpySlot();
        $slot2 = new SpySlot();
        $slot3 = new SpySlot();

        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore
            ->expects(self::exactly(3))
            ->method('acquire')
            ->willReturnOnConsecutiveCalls($slot1, $slot2, $slot3);

        $client = new MockHttpClient([
            new MockResponse('a', ['http_code' => 200]),
            new MockResponse('b', ['http_code' => 200]),
            new MockResponse('c', ['http_code' => 200]),
        ]);
        $throttling = new MailchimpThrottlingHttpClient($client, $semaphore);

        $responses = [
            $throttling->request('GET', 'https://api.mailchimp.test/a'),
            $throttling->request('GET', 'https://api.mailchimp.test/b'),
            $throttling->request('GET', 'https://api.mailchimp.test/c'),
        ];

        $bodies = [];
        foreach ($throttling->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                $bodies[] = $response->getContent();
            }
        }

        self::assertSame(['a', 'b', 'c'], $bodies);
        self::assertSame(1, $slot1->releaseCount);
        self::assertSame(1, $slot2->releaseCount);
        self::assertSame(1, $slot3->releaseCount);
    }

    public function testRequestWhenSemaphoreThrowsTimeoutPropagatesException(): void
    {
        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore
            ->expects(self::once())
            ->method('acquire')
            ->willThrowException(new MailchimpConcurrencyTimeoutException(10_000));

        $mockClient = new MockHttpClient(static function (): MockResponse {
            self::fail('HTTP client must not be hit when semaphore acquire fails');
        });

        $throttling = new MailchimpThrottlingHttpClient($mockClient, $semaphore);

        $this->expectException(MailchimpConcurrencyTimeoutException::class);
        $throttling->request('GET', 'https://api.mailchimp.test/3.0/lists');
    }
}

final class SpySlot implements MailchimpSlot
{
    public int $releaseCount = 0;
    private bool $released = false;

    public function release(): void
    {
        if ($this->released) {
            return;
        }
        $this->released = true;
        ++$this->releaseCount;
    }
}
