<?php

declare(strict_types=1);

namespace Tests\App\Chatbot\Usage;

use App\Chatbot\Platform\UsageCapturingResultConverter;
use App\Chatbot\Usage\ChatbotUsageTracker;
use App\Chatbot\Usage\UsagePlatformSubscriber;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\Generic\Completions\ResultConverter;
use Symfony\AI\Platform\Bridge\Generic\CompletionsModel;
use Symfony\AI\Platform\Event\ResultEvent;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\Result\DeferredResult;
use Symfony\AI\Platform\Result\RawResultInterface;

#[Group('functional')]
#[Group('chatbot')]
class UsagePlatformSubscriberTest extends TestCase
{
    public function testCaptureUsageSwapsResultConverter(): void
    {
        $deferred = new DeferredResult(new ResultConverter(), $this->createStub(RawResultInterface::class));
        $event = new ResultEvent(new CompletionsModel('antiseche-rag'), $deferred, ['stream' => true]);

        $this->createSubscriber()->captureUsage($event);

        self::assertInstanceOf(UsageCapturingResultConverter::class, $event->getDeferredResult()->getResultConverter());
        self::assertSame($deferred->getRawResult(), $event->getDeferredResult()->getRawResult());
    }

    public function testCaptureUsageIgnoresOtherModelTypes(): void
    {
        $deferred = new DeferredResult(new ResultConverter(), $this->createStub(RawResultInterface::class));
        $event = new ResultEvent(new Model('gemini-3-flash-preview'), $deferred, []);

        $this->createSubscriber()->captureUsage($event);

        self::assertSame($deferred, $event->getDeferredResult());
    }

    private function createSubscriber(): UsagePlatformSubscriber
    {
        return new UsagePlatformSubscriber(new ChatbotUsageTracker());
    }
}
