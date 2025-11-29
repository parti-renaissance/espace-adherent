<?php

declare(strict_types=1);

namespace App\AdherentMessage\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\AdherentMessage\Command\SynchronizeDynamicSegmentCommand;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostWriteAudienceSegmentListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['dispatchPostChangeEvent', EventPriorities::POST_WRITE]];
    }

    public function dispatchPostChangeEvent(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $audienceSegment = $event->getControllerResult();

        if (
            !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
            || !$audienceSegment instanceof AudienceSegment
        ) {
            return;
        }

        if (!$audienceSegment->isSynchronized()) {
            $this->bus->dispatch(new SynchronizeDynamicSegmentCommand($audienceSegment->getUuid(), $audienceSegment::class));
        }
    }
}
