<?php

declare(strict_types=1);

namespace Tests\App\Chatbot\Usage;

use App\Chatbot\Usage\ChatbotUsageTracker;
use App\Chatbot\Usage\RecordUsageCommand;
use App\Entity\Chatbot\Message;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[Group('functional')]
#[Group('chatbot')]
class ChatbotUsageTrackerTest extends TestCase
{
    private ?RecordUsageCommand $dispatched = null;

    protected function setUp(): void
    {
        $this->dispatched = null;
    }

    public function testRecordDispatchesCapturedUsageForTrackedAgent(): void
    {
        $tracker = $this->createTracker(['antiseche']);

        $tracker->start();
        $tracker->capture(['prompt_tokens' => 9200, 'total_tokens' => 9620]);
        $tracker->record($this->createMessage(7), 'antiseche');

        self::assertNotNull($this->dispatched);
        self::assertSame(7, $this->dispatched->messageId);
        self::assertSame(['prompt_tokens' => 9200, 'total_tokens' => 9620], $this->dispatched->rawUsage);
        self::assertNotNull($this->dispatched->responseTimeMs);
        self::assertGreaterThanOrEqual(0, $this->dispatched->responseTimeMs);
    }

    public function testRecordWithoutStartDispatchesNullResponseTime(): void
    {
        $tracker = $this->createTracker(['antiseche']);

        $tracker->record($this->createMessage(7), 'antiseche');

        self::assertNotNull($this->dispatched);
        self::assertNull($this->dispatched->responseTimeMs);
    }

    public function testRecordDispatchesNullUsageWhenNothingCaptured(): void
    {
        $tracker = $this->createTracker(['antiseche']);

        $tracker->record($this->createMessage(7), 'antiseche');

        self::assertNotNull($this->dispatched);
        self::assertNull($this->dispatched->rawUsage);
    }

    public function testRecordSkipsUntrackedAgentAndResetsCapturedUsage(): void
    {
        $tracker = $this->createTracker(['antiseche']);

        $tracker->capture(['total_tokens' => 10]);
        $tracker->record($this->createMessage(7), 'chatbot');

        self::assertNull($this->dispatched);

        $tracker->record($this->createMessage(7), 'antiseche');

        self::assertNotNull($this->dispatched);
        self::assertNull($this->dispatched->rawUsage);
    }

    private function createTracker(array $usageTrackedAgentModels): ChatbotUsageTracker
    {
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(function (RecordUsageCommand $message): Envelope {
            $this->dispatched = $message;

            return new Envelope($message);
        });

        return new ChatbotUsageTracker($bus, $usageTrackedAgentModels);
    }

    private function createMessage(int $id): Message
    {
        $message = $this->createStub(Message::class);
        $message->method('getId')->willReturn($id);

        return $message;
    }
}
