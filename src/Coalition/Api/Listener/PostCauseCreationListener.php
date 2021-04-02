<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Coalition\Cause;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostCauseCreationListener implements EventSubscriberInterface
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['attachFirstCauseFollower', EventPriorities::POST_WRITE]];
    }

    public function attachFirstCauseFollower(ViewEvent $event): void
    {
        $cause = $event->getControllerResult();

        if (!$event->getRequest()->isMethod(Request::METHOD_POST) || !$cause instanceof Cause) {
            return;
        }

        $this->manager->persist($cause->createFollower($cause->getAuthor()));
        $this->manager->flush();
    }
}
