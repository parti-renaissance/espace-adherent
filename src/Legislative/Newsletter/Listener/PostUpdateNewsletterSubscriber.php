<?php

declare(strict_types=1);

namespace App\Legislative\Newsletter\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\LegislativeNewsletterSubscription;
use App\Newsletter\Command\MailchimpSyncLegislativeNewsletterCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostUpdateNewsletterSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onNewsletterUpdate', EventPriorities::POST_WRITE]];
    }

    public function onNewsletterUpdate(ViewEvent $event): void
    {
        /** @var LegislativeNewsletterSubscription $newsletter */
        $newsletter = $event->getControllerResult();
        $request = $event->getRequest();

        if (
            !\in_array($request->getMethod(), [Request::METHOD_POST])
            || !$newsletter instanceof LegislativeNewsletterSubscription
            || !$newsletter->getConfirmedAt()
        ) {
            return;
        }

        $this->bus->dispatch(new MailchimpSyncLegislativeNewsletterCommand($newsletter->getId()));
    }
}
