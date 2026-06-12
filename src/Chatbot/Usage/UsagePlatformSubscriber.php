<?php

declare(strict_types=1);

namespace App\Chatbot\Usage;

use App\Chatbot\Platform\UsageCapturingResultConverter;
use Symfony\AI\Platform\Bridge\Generic\CompletionsModel;
use Symfony\AI\Platform\Event\InvocationEvent;
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
            InvocationEvent::class => 'requestUsage',
            ResultEvent::class => 'captureUsage',
        ];
    }

    public function requestUsage(InvocationEvent $event): void
    {
        $model = $event->getModel();

        if (!$model instanceof CompletionsModel) {
            return;
        }

        $options = $event->getOptions();

        if (!($options['stream'] ?? $model->getOptions()['stream'] ?? false)) {
            return;
        }

        $options['stream_options'] = ($options['stream_options'] ?? []) + ['include_usage' => true];
        $event->setOptions($options);
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
