<?php

declare(strict_types=1);

namespace App\Procuration\V2\Listener;

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

            ProcurationEvents::REQUEST_CREATED => ['updateZoneIds'],
            ProcurationEvents::REQUEST_AFTER_UPDATE => ['updateZoneIds'],
        ];
    }

    public function updateZoneIds(ProcurationEvent $event): void
    {
        $proxy = $event->procuration;

        $proxy->refreshZoneIds();

        $this->entityManager->flush();
    }
}
