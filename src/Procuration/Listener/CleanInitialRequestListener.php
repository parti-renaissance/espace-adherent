<?php

declare(strict_types=1);

namespace App\Procuration\Listener;

use App\Procuration\Event\ProcurationEvent;
use App\Procuration\Event\ProcurationEvents;
use App\Repository\Procuration\ProcurationRequestRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CleanInitialRequestListener implements EventSubscriberInterface
{
    public function __construct(private readonly ProcurationRequestRepository $procurationRequestRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::PROXY_CREATED => ['cleanInitialRequests', -255],
            ProcurationEvents::REQUEST_CREATED => ['cleanInitialRequests', -255],
        ];
    }

    public function cleanInitialRequests(ProcurationEvent $event): void
    {
        $procuration = $event->procuration;

        $this->procurationRequestRepository
            ->createQueryBuilder('pr')
            ->delete()
            ->where('pr.email = :email')
            ->setParameters([
                'email' => $procuration->email,
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
