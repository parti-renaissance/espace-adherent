<?php

namespace App\Jecoute\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Jecoute\Riposte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostGetRipostesListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['dispatchPostReadEvent', EventPriorities::POST_READ]];
    }

    public function dispatchPostReadEvent(RequestEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        if ('api_ripostes_get_collection' !== $attributes->get('_route')
            || Riposte::class !== $attributes->get('_api_resource_class')) {
            return;
        }

        /** @var Riposte $riposte */
        foreach ($attributes->get('data') as $riposte) {
            $riposte->incrementNbViews();
        }

        $this->entityManager->flush();
    }
}
