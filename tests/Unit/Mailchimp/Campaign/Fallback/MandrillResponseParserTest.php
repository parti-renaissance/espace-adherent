<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Fallback;

use App\Mailchimp\Campaign\Fallback\MandrillResponseParser;
use PHPUnit\Framework\TestCase;

class MandrillResponseParserTest extends TestCase
{
    public function testCountsStatusesPerRecipient(): void
    {
        $body = json_encode([
            ['email' => 'a@test.dev', 'status' => 'sent'],
            ['email' => 'b@test.dev', 'status' => 'queued'],
            ['email' => 'c@test.dev', 'status' => 'rejected', 'reject_reason' => 'hard-bounce'],
            ['email' => 'd@test.dev', 'status' => 'invalid'],
            ['email' => 'e@test.dev', 'status' => 'scheduled'],
        ]);

        $result = new MandrillResponseParser()->parse($body);

        self::assertSame(1, $result->sent);
        self::assertSame(2, $result->queued); // queued + scheduled
        self::assertSame(1, $result->rejected);
        self::assertSame(1, $result->invalid);
        self::assertSame(5, $result->total());
        self::assertSame(['c@test.dev', 'd@test.dev'], $result->rejectedEmails);
    }

    public function testRejectionRate(): void
    {
        $body = json_encode([
            ['email' => 'a@test.dev', 'status' => 'sent'],
            ['email' => 'b@test.dev', 'status' => 'rejected'],
            ['email' => 'c@test.dev', 'status' => 'invalid'],
            ['email' => 'd@test.dev', 'status' => 'sent'],
        ]);

        $result = new MandrillResponseParser()->parse($body);

        self::assertSame(0.5, $result->rejectionRate());
    }

    public function testInvalidJsonReturnsZeroes(): void
    {
        $result = new MandrillResponseParser()->parse('not-json');

        self::assertSame(0, $result->total());
        self::assertSame(0.0, $result->rejectionRate());
    }
}
