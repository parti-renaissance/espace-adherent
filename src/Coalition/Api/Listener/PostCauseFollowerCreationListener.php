<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Coalition\MessageNotifier;
use App\Entity\Coalition\CauseFollower;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostCauseFollowerCreationListener implements EventSubscriberInterface
{
    private $notifier;

    public function __construct(EntityManagerInterface $manager, MessageNotifier $notifier)
    {
        $this->notifier = $notifier;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['sendConfirmationEmail', EventPriorities::POST_WRITE]];
    }

    public function sendConfirmationEmail(ViewEvent $event): void
    {
        $causeFollower = $event->getControllerResult();

        if (!$event->getRequest()->isMethod(Request::METHOD_PUT) || !$causeFollower instanceof CauseFollower) {
            return;
        }

        $this->notifier->sendCauseFollowerConfirmationMessage($causeFollower);
    }
}
