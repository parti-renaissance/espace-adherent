<?php

namespace AppBundle\ApplicationRequest\Listener;

use AppBundle\ApplicationRequest\ApplicationRequestEvent;
use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\ApplicationRequest\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HideDuplicateRequestsSubscriber implements EventSubscriberInterface
{
    /**
     * @var ApplicationRequestRepository
     */
    private $repository;

    public function __construct(ApplicationRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CREATED => ['hideDuplicateRequests', -1],
        ];
    }

    public function hideDuplicateRequests(ApplicationRequestEvent $event): void
    {
        $this->repository->hideDuplicates($event->getApplicationRequest());
    }
}
