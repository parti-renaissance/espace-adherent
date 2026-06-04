<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\JeMengage\Timeline\Indexer\IndexerClient;
use App\JeMengage\Timeline\Indexer\ItemPayload;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class IndexerClientTest extends TestCase
{
    public function testIndexPostsSingleItemToIndexEndpoint(): void
    {
        $requests = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = [$method, $url, $options];

            return new MockResponse('{"external_id":"evt-1","hot_score":1.2,"created":true}');
        }, 'https://indexer.test');

        new IndexerClient($http, 'https://indexer.test', new NullLogger())->index($this->payload());

        self::assertCount(1, $requests);
        [$method, $url, $options] = $requests[0];
        self::assertSame('POST', $method);
        self::assertSame('https://indexer.test/index', $url);
        self::assertStringContainsString('application/json', implode("\n", $options['headers']));
        self::assertSame('evt-1', json_decode($options['body'], true)['external_id']);
    }

    public function testIndexBatchPostsBareArray(): void
    {
        $requests = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = [$url, $options];

            return new MockResponse('[]');
        }, 'https://indexer.test');

        new IndexerClient($http, 'https://indexer.test', new NullLogger())->indexBatch([$this->payload('a'), $this->payload('b')]);

        [$url, $options] = $requests[0];
        self::assertSame('https://indexer.test/index/batch', $url);
        $body = json_decode($options['body'], true);
        self::assertCount(2, $body);
        self::assertSame('a', $body[0]['external_id']);
        self::assertSame('b', $body[1]['external_id']);
    }

    public function testEmptyBatchSendsNoRequest(): void
    {
        $requests = 0;
        $http = new MockHttpClient(function () use (&$requests) {
            ++$requests;

            return new MockResponse('[]');
        }, 'https://indexer.test');

        new IndexerClient($http, 'https://indexer.test', new NullLogger())->indexBatch([]);

        self::assertSame(0, $requests);
    }

    public function testRetryableStatusThrows(): void
    {
        $http = new MockHttpClient(new MockResponse('', ['http_code' => 503]), 'https://indexer.test');

        $this->expectException(\RuntimeException::class);

        new IndexerClient($http, 'https://indexer.test', new NullLogger())->index($this->payload());
    }

    public function testNonRetryableStatusIsLoggedNotThrown(): void
    {
        $http = new MockHttpClient(new MockResponse('invalid', ['http_code' => 422]), 'https://indexer.test');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        new IndexerClient($http, 'https://indexer.test', $logger)->index($this->payload());
    }

    public function testNoRequestWhenIndexerUrlNotConfigured(): void
    {
        $requests = 0;
        $http = new MockHttpClient(function () use (&$requests) {
            ++$requests;

            return new MockResponse('');
        }, 'https://indexer.test');

        new IndexerClient($http, '', new NullLogger())->index($this->payload());

        self::assertSame(0, $requests, 'Empty TIMELINE_INDEXER_URL disables the push (no-op).');
    }

    private function payload(string $externalId = 'evt-1'): ItemPayload
    {
        return new ItemPayload($externalId, 'event', new \DateTimeImmutable('2026-05-20T10:00:00+00:00'), null, 1, null);
    }
}
