<?php

namespace App\Agora\Handler;

use App\Agora\Command\RemoveAdherentForAllFuturAgoraEventCommand;
use App\Algolia\AlgoliaIndexedEntityManager;
use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\Event\RegistrationStatusEnum;
use App\Repository\AdherentRepository;
use App\Repository\AgoraRepository;
use App\Repository\Event\EventRepository;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveAdherentForAllFuturAgoraEventCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly AgoraRepository $agoraRepository,
        private readonly AlgoliaIndexedEntityManager $algoliaManager,
        private readonly EventRepository $eventRepository,
    ) {
    }

    public function __invoke(RemoveAdherentForAllFuturAgoraEventCommand $command): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        /** @var Agora $agora */
        if (!$agora = $this->agoraRepository->find($command->agoraId)) {
            return;
        }

        $this->eventRegistrationRepository->removeAllForFuturAgoraEvents($agora, $adherent, new \DateTime(), RegistrationStatusEnum::INVITED);

        if ($events = $this->eventRepository->findAllFuturAgoraEvents($agora, new \DateTime())) {
            $this->algoliaManager->batch($events);
        }
    }
}
