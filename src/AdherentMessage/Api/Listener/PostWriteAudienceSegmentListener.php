<?php

namespace App\AdherentMessage\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\AdherentMessage\Segment\DynamicSegmentEvent;
use App\AdherentMessage\Segment\DynamicSegmentEvents;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PostWriteAudienceSegmentListener implements EventSubscriberInterface
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
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

        $this->eventDispatcher->dispatch(new DynamicSegmentEvent($audienceSegment), DynamicSegmentEvents::POST_CHANGE);
    }
}
