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

        $response = new TimelineRankerClient($http)->getItems($this->profile(), ['cand-1']);

        self::assertCount(1, $requests);
        [$method, $url, $options] = $requests[0];
        self::assertSame('POST', $method);
        self::assertSame('https://ranker.test/get_items', $url);
        self::assertSame('42', json_decode($options['body'], true)['user_id']);
        self::assertSame(['a', 'b'], $response->getExternalIds());
    }

    public function testGetItemsSendsCandidateIds(): void
    {
        $requests = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = $options;

            return new MockResponse('{"items":[]}');
        }, 'https://ranker.test');

        new TimelineRankerClient($http)->getItems($this->profile(), ['cand-1', 'cand-2']);

        // The locally authorized candidate set rides in the same JSON body as the profile, as a list.
        self::assertSame(['cand-1', 'cand-2'], json_decode($requests[0]['body'], true)['candidate_ids']);
    }

    public function testGetItemsForwardsSessionIdAlongsideProfile(): void
    {
        $requests = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = $options;

            return new MockResponse('{"items":[]}');
        }, 'https://ranker.test');

        new TimelineRankerClient($http)->getItems($this->profile(), ['cand-1'], 'sess-123');

        // session_id rides as a sibling of the (server-derived) profile fields in the same JSON body.
        $body = json_decode($requests[0]['body'], true);
        self::assertSame('sess-123', $body['session_id']);
        self::assertSame('42', $body['user_id']);
    }

    public function testGetItemsOmitsSessionIdWhenNotProvided(): void
    {
        $requests = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = $options;

            return new MockResponse('{"items":[]}');
        }, 'https://ranker.test');

        new TimelineRankerClient($http)->getItems($this->profile(), ['cand-1']);

        self::assertArrayNotHasKey('session_id', json_decode($requests[0]['body'], true));
    }

    public function testGetItemsThrowsOnServerError(): void
    {
        $http = new MockHttpClient(new MockResponse('', ['http_code' => 503]), 'https://ranker.test');

        $this->expectException(\RuntimeException::class);

        new TimelineRankerClient($http)->getItems($this->profile(), ['cand-1']);
    }

    public function testGetItemsThrowsOnInvalidPayload(): void
    {
        $http = new MockHttpClient(new MockResponse('{"foo":1}'), 'https://ranker.test');

        $this->expectException(\RuntimeException::class);

        new TimelineRankerClient($http)->getItems($this->profile(), ['cand-1']);
    }

    public function testGetItemsThrowsOnEmptyBody(): void
    {
        // An empty (or non-JSON) 200 body is an invalid payload: it must surface as a RuntimeException
        // so the controller falls back to Algolia instead of 500-ing on the underlying JsonException.
        $http = new MockHttpClient(new MockResponse(''), 'https://ranker.test');

        $this->expectException(\RuntimeException::class);

        new TimelineRankerClient($http)->getItems($this->profile(), ['cand-1']);
    }

    private function profile(): UserProfile
    {
        return new UserProfile(42, [], [], [], [], [], [], 0, []);
    }
}
