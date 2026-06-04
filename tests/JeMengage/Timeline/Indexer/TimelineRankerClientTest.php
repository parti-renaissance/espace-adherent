<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\JeMengage\Timeline\Indexer\TimelineRankerClient;
use App\JeMengage\Timeline\Indexer\UserProfile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TimelineRankerClientTest extends TestCase
{
    public function testGetItemsPostsProfileToGetItemsAndReturnsOrderedItems(): void
    {
        $requests = [];
        // base_uri lives in the scoped client config; the client sends the "/get_items" path against it.
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = [$method, $url, $options];

            return new MockResponse('{"items":[{"external_id":"a","kind":"event","hot_score":1.7},{"external_id":"b","kind":"publication","hot_score":0.9}]}');
        }, 'https://ranker.test');

        $response = new TimelineRankerClient($http)->getItems($this->profile());

        self::assertCount(1, $requests);
        [$method, $url, $options] = $requests[0];
        self::assertSame('POST', $method);
        self::assertSame('https://ranker.test/get_items', $url);
        self::assertSame('42', json_decode($options['body'], true)['user_id']);
        self::assertSame(['a', 'b'], $response->getExternalIds());
    }

    public function testGetItemsThrowsOnServerError(): void
    {
        $http = new MockHttpClient(new MockResponse('', ['http_code' => 503]), 'https://ranker.test');

        $this->expectException(\RuntimeException::class);

        new TimelineRankerClient($http)->getItems($this->profile());
    }

    public function testGetItemsThrowsOnInvalidPayload(): void
    {
        $http = new MockHttpClient(new MockResponse('{"foo":1}'), 'https://ranker.test');

        $this->expectException(\RuntimeException::class);

        new TimelineRankerClient($http)->getItems($this->profile());
    }

    private function profile(): UserProfile
    {
        return new UserProfile(42, [], [], [], [], [], [], 0, []);
    }
}
