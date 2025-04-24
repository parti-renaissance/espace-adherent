<?php

namespace App\Jecoute\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Jecoute\Riposte;
use App\OAuth\Model\Scope;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostGetRipostesListener implements EventSubscriberInterface
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['dispatchPostReadEvent', EventPriorities::POST_READ]];
    }

    public function dispatchPostReadEvent(RequestEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        if ('_api_/v3/ripostes_get_collection' !== $attributes->get('_route')
            || Riposte::class !== $attributes->get('_api_resource_class')
            || !$this->security->isGranted(Scope::generateRole(Scope::JEMARCHE_APP))
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
