<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Coalition\CauseFollower;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CoalitionFollowerPostCauseFollowerCreationListener implements EventSubscriberInterface
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['createCoalitionFollower', EventPriorities::POST_WRITE]];
    }

    public function createCoalitionFollower(ViewEvent $event): void
    {
        $causeFollower = $event->getControllerResult();

        if (!$event->getRequest()->isMethod(Request::METHOD_PUT) || !$causeFollower instanceof CauseFollower) {
            return;
        }

        $adherent = $causeFollower->getAdherent();
        if (!$adherent->isCoalitionSubscription() || ($adherent->isAdherent() && !$adherent->isCoalitionsCguAccepted())) {
            return;
        }

        $coalition = $causeFollower->getCause()->getCoalition();
        if (!$coalition->hasFollower($adherent)) {
            $coalition->createFollower($adherent);

            $this->manager->persist($coalition->createFollower($adherent));
            $this->manager->flush();
        }
    }
}
