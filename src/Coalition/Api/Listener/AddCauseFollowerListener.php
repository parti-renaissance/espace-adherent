<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Coalition\CauseFollowerChangeEvent;
use App\Coalition\Events;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CauseFollower;
use App\Repository\Coalition\CauseFollowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AddCauseFollowerListener implements EventSubscriberInterface
{
    private $entityManager;
    private $causeFollowerRepository;

    public function __construct(EntityManagerInterface $entityManager, CauseFollowerRepository $causeFollowerRepository)
    {
        $this->entityManager = $entityManager;
        $this->causeFollowerRepository = $causeFollowerRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CAUSE_FOLLOWER_ADDED => 'updateFollowersCount',
            KernelEvents::VIEW => ['updateFollowersCountForApip', EventPriorities::POST_WRITE],
        ];
    }

    public function updateFollowersCount(CauseFollowerChangeEvent $event): void
    {
        $this->updateCount($event->getCause());
    }

    public function updateFollowersCountForApip(ViewEvent $event): void
    {
        $causeFollower = $event->getControllerResult();

        if (!$event->getRequest()->isMethod(Request::METHOD_PUT)
            || !$causeFollower instanceof CauseFollower) {
            return;
        }

        $this->updateCount($causeFollower->getCause());
    }

    private function updateCount(Cause $cause): void
    {
        $cause->setFollowersCount($this->causeFollowerRepository->countForCause($cause));

        $this->entityManager->flush();
    }
}
