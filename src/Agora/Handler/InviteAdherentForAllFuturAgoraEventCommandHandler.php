<?php

namespace App\Agora\Handler;

use App\Agora\Command\InviteAdherentForAllFuturAgoraEventCommand;
use App\Agora\Notifier;
use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\Event\RegistrationStatusEnum;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Repository\AdherentRepository;
use App\Repository\AgoraRepository;
use App\Repository\Event\EventRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InviteAdherentForAllFuturAgoraEventCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EventRepository $eventRepository,
        private readonly AgoraRepository $agoraRepository,
        private readonly EventRegistrationCommandHandler $handler,
        private readonly Notifier $notifier,
    ) {
    }

    public function __invoke(InviteAdherentForAllFuturAgoraEventCommand $command): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if (!$adherent->isEnabled()) {
            return;
        }

        /** @var Agora $agora */
        if (!$agora = $this->agoraRepository->find($command->agoraId)) {
            return;
        }

        if (!$agora->published) {
            return;
        }

        $events = $this->eventRepository->findAllFuturAgoraEvents($agora, $adherent, new \DateTime());

        foreach ($events as $event) {
            if ($this->handler->handle(new EventRegistrationCommand($event, $adherent, RegistrationStatusEnum::INVITED), false)) {
                $this->notifier->sendEventInvitation($event, [$adherent]);
            }
        }
    }
}
