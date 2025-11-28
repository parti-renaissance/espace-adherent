<?php

declare(strict_types=1);

namespace App\Procuration\V2\Listener;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Entity\Adherent;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentRelationListener implements EventSubscriberInterface
{
    private ?Adherent $adherent = null;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::REQUEST_CREATED => ['onAfterUpdate'],
            ProcurationEvents::REQUEST_BEFORE_UPDATE => ['onBeforeUpdate'],
            ProcurationEvents::REQUEST_AFTER_UPDATE => ['onAfterUpdate'],
            ProcurationEvents::PROXY_CREATED => ['onAfterUpdate'],
            ProcurationEvents::PROXY_BEFORE_UPDATE => ['onBeforeUpdate'],
            ProcurationEvents::PROXY_AFTER_UPDATE => ['onAfterUpdate'],
        ];
    }

    public function onBeforeUpdate(ProcurationEvent $event): void
    {
        $this->adherent = $event->procuration->adherent;
    }

    public function onAfterUpdate(ProcurationEvent $event): void
    {
        $procuration = $event->procuration;

        $procuration->adherent = $this->adherentRepository->findOneByEmail($procuration->email);

        $this->entityManager->flush();

        if ($this->adherent === $procuration->adherent) {
            return;
        }

        if ($this->adherent) {
            $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($this->adherent->getUuid()));
        }

        if ($procuration->adherent) {
            $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($procuration->adherent->getUuid()));
        }
    }
}
