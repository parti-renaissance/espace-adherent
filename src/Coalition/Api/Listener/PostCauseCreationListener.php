<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Coalition\MessageNotifier;
use App\Entity\Coalition\Cause;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostCauseCreationListener implements EventSubscriberInterface
{
    private $manager;
    private $notifier;

    public function __construct(EntityManagerInterface $manager, MessageNotifier $notifier)
    {
        $this->manager = $manager;
        $this->notifier = $notifier;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['attachFirstCauseFollower', EventPriorities::POST_WRITE]];
    }

    public function attachFirstCauseFollower(ViewEvent $event): void
    {
        $cause = $event->getControllerResult();

        if (!$event->getRequest()->isMethod(Request::METHOD_POST) || !$cause instanceof Cause) {
            return;
        }

        $cause->setFollowersCount(1);

        $adherent = $cause->getAuthor();
        if ($adherent->isCoalitionSubscription() && (!$adherent->isAdherent() || $adherent->isCoalitionsCguAccepted())) {
            $coalition = $cause->getCoalition();
            $this->manager->persist($coalition->createFollower($adherent));
        }

        $this->manager->persist($cause->createFollower($adherent));
        $this->manager->flush();

        $this->sendConfirmationEmail($cause);
    }

    private function sendConfirmationEmail(Cause $cause): void
    {
        $this->notifier->sendCauseCreationConfirmationMessage($cause);
    }
}
