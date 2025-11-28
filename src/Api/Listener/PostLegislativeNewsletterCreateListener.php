<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\LegislativeNewsletterSubscription;
use App\Legislative\Newsletter\Events;
use App\Legislative\Newsletter\LegislativeNewsletterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PostLegislativeNewsletterCreateListener implements EventSubscriberInterface
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onSubscribe', EventPriorities::POST_WRITE]];
    }

    public function onSubscribe(ViewEvent $viewEvent): void
    {
        $subscription = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();

        if (
            !$subscription instanceof LegislativeNewsletterSubscription
            || $subscription->getConfirmedAt()
            || Request::METHOD_POST !== $request->getMethod()
        ) {
            return;
        }

        $this->dispatcher->dispatch(
            new LegislativeNewsletterEvent($subscription),
            Events::NEWSLETTER_SUBSCRIBE
        );
    }
}
