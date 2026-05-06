<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp;

use App\Mailchimp\Driver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DriverGetAllSegmentsWithPrefixTest extends TestCase
{
    private const string LIST_ID = 'e890e2dc99';

    public function testGetAllSegmentsWithPrefixFiltersByPrefixOnlyMatchingYielded(): void
    {
        $body = json_encode([
            'segments' => [
                ['id' => 1, 'name' => 'campaign_aaa', 'created_at' => '2026-05-01T10:00:00+00:00'],
                ['id' => 2, 'name' => 'manual_segment', 'created_at' => '2026-05-01T10:00:00+00:00'],
                ['id' => 3, 'name' => 'campaign_bbb', 'created_at' => '2026-05-02T10:00:00+00:00'],
            ],
            'total_items' => 3,
        ]);

        $driver = $this->buildDriver([new MockResponse($body, ['http_code' => 200])]);
        $segments = iterator_to_array($driver->getAllSegmentsWithPrefix('campaign_', self::LIST_ID), false);

        self::assertCount(2, $segments);
        self::assertSame(1, $segments[0]['id']);
        self::assertSame('campaign_aaa', $segments[0]['name']);
        self::assertSame(3, $segments[1]['id']);
    }

    public function testGetAllSegmentsWithPrefixPaginationIteratesUntilTotalReached(): void
    {
        // total_items = 3 across 2 pages of size 2
        $page1 = json_encode([
            'segments' => [
                ['id' => 1, 'name' => 'campaign_aaa', 'created_at' => '2026-05-01T10:00:00+00:00'],
                ['id' => 2, 'name' => 'campaign_bbb', 'created_at' => '2026-05-01T10:00:00+00:00'],
            ],
            'total_items' => 3,
        ]);
        $page2 = json_encode([
            'segments' => [
                ['id' => 3, 'name' => 'campaign_ccc', 'created_at' => '2026-05-02T10:00:00+00:00'],
            ],
            'total_items' => 3,
        ]);

        $driver = $this->buildDriver([
            new MockResponse($page1, ['http_code' => 200]),
            new MockResponse($page2, ['http_code' => 200]),
        ]);

        $segments = iterator_to_array($driver->getAllSegmentsWithPrefix('campaign_', self::LIST_ID, 2), false);

        self::assertCount(3, $segments);
        self::assertSame([1, 2, 3], array_map(static fn (array $s): int => $s['id'], $segments));
    }

    public function testGetAllSegmentsWithPrefixApiErrorStopsAndYieldsNothing(): void
    {
        $driver = $this->buildDriver([new MockResponse('boom', ['http_code' => 500])]);
        $segments = iterator_to_array($driver->getAllSegmentsWithPrefix('campaign_', self::LIST_ID), false);

        self::assertSame([], $segments);
    }

    private function buildDriver(array $responses): Driver
    {
        $client = new MockHttpClient($responses, 'https://us16.api.mailchimp.com');

        return new Driver($client, self::LIST_ID);
    }
}
