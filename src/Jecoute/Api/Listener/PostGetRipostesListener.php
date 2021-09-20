<?php

namespace App\Jecoute\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Jecoute\Riposte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class PostGetRipostesListener implements EventSubscriberInterface
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['dispatchPostReadEvent', EventPriorities::POST_READ]];
    }

    public function dispatchPostReadEvent(RequestEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        if ('api_ripostes_get_collection' !== $attributes->get('_route')
            || Riposte::class !== $attributes->get('_api_resource_class')
            || !$this->security->isGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')
        ) {
            return;
        }

        /** @var Riposte $riposte */
        foreach ($attributes->get('data') as $riposte) {
            $riposte->incrementNbViews();
        }

        $this->entityManager->flush();
    }
}
