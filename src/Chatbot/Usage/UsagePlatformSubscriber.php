<?php

declare(strict_types=1);

namespace App\Chatbot\Usage;

use App\Chatbot\Platform\UsageCapturingResultConverter;
use Symfony\AI\Platform\Bridge\Generic\CompletionsModel;
use Symfony\AI\Platform\Event\ResultEvent;
use Symfony\AI\Platform\Result\DeferredResult;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UsagePlatformSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ChatbotUsageTracker $usageTracker)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResultEvent::class => 'captureUsage',
        ];
    }

    public function captureUsage(ResultEvent $event): void
    {
        if (!$event->getModel() instanceof CompletionsModel) {
            return;
        }

        $event->setDeferredResult(new DeferredResult(
            new UsageCapturingResultConverter($this->usageTracker),
            $event->getDeferredResult()->getRawResult(),
            $event->getOptions(),
        ));
    }
}
