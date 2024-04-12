<?php

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Proxy;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateProxyZoneIdsListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::PROXY_CREATED => ['updateZoneIds'],
            ProcurationEvents::PROXY_AFTER_UPDATE => ['updateZoneIds'],
        ];
    }

    public function updateZoneIds(ProcurationEvent $event): void
    {
        $proxy = $event->procuration;

        if (!$proxy instanceof Proxy) {
            return;
        }

        $proxy->refreshZoneIds();

        $this->entityManager->flush();
    }
}
