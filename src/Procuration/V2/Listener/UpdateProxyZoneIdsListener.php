<?php

namespace App\Procuration\V2\Listener;

use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\Event\ProxyEvent;
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

    public function updateZoneIds(ProxyEvent $event): void
    {
        $event->proxy->refreshZoneIds();

        $this->entityManager->flush();
    }
}
