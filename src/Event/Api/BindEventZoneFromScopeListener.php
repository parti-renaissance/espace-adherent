<?php

namespace App\Event\Api;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Event\BaseEvent;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BindEventZoneFromScopeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE]];
    }

    public function onPostWrite(ViewEvent $viewEvent): void
    {
        /** @var BaseEvent $event */
        $event = $viewEvent->getControllerResult();

        if (!$event instanceof BaseEvent || !$event->getZones()->isEmpty()) {
            return;
        }

        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope || !$scope->getZones()) {
            return;
        }

        $event->setZones($scope->getZones());

        $this->entityManager->flush();
    }
}
