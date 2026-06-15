<?php

declare(strict_types=1);

namespace Tests\App\Chatbot\Usage;

use App\Chatbot\Usage\ChatbotUsageTracker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('chatbot')]
class ChatbotUsageTrackerTest extends TestCase
{
    public function testPullReturnsCapturedUsage(): void
    {
        $tracker = new ChatbotUsageTracker();
        $tracker->capture(['prompt_tokens' => 9200, 'total_tokens' => 9620]);

        self::assertSame(['prompt_tokens' => 9200, 'total_tokens' => 9620], $tracker->pull());
    }

    public function testPullClearsStateSoSecondPullReturnsNull(): void
    {
        $tracker = new ChatbotUsageTracker();
        $tracker->capture(['total_tokens' => 10]);

        $tracker->pull();

        self::assertNull($tracker->pull());
    }

    public function testPullWithoutCaptureReturnsNull(): void
    {
        self::assertNull(new ChatbotUsageTracker()->pull());
    }

    public function testResetClearsCapturedUsage(): void
    {
        $tracker = new ChatbotUsageTracker();
        $tracker->capture(['total_tokens' => 10]);

        $tracker->reset();

        self::assertNull($tracker->pull());
    }
}
