<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CauseFollower;
use App\Repository\Coalition\CauseFollowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RemoveCauseFollowerListener implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['updateFollowersCount', EventPriorities::PRE_WRITE],
        ];
    }

    public function updateFollowersCount(ViewEvent $event): void
    {
        $causeFollower = $event->getControllerResult();

        if (!$event->getRequest()->isMethod(Request::METHOD_DELETE)
            || !$causeFollower instanceof CauseFollower) {
            return;
        }

        $this->updateCount($causeFollower->getCause());
    }

    private function updateCount(Cause $cause): void
    {
        $count = $this->causeFollowerRepository->countForCause($cause);
        $cause->setFollowersCount(--$count);

        $this->entityManager->flush();
    }
}
