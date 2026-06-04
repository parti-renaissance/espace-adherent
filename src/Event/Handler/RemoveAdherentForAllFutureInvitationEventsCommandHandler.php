<?php

declare(strict_types=1);

namespace App\Event\Handler;

use App\Algolia\AlgoliaIndexerInterface;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Entity\Event\RegistrationStatusEnum;
use App\Event\Command\RemoveAdherentForAllFutureInvitationEventsCommand;
use App\Repository\AdherentRepository;
use App\Repository\AgoraRepository;
use App\Repository\CommitteeRepository;
use App\Repository\Event\EventRepository;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveAdherentForAllFutureInvitationEventsCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly AgoraRepository $agoraRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly EventRepository $eventRepository,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly AlgoliaIndexerInterface $algoliaManager,
    ) {
    }

    public function __invoke(RemoveAdherentForAllFutureInvitationEventsCommand $command): void
    {
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid()->toRfc4122())) {
            return;
        }

        $container = $this->resolveContainer($command);

        if (null === $container) {
            return;
        }

        $this->eventRegistrationRepository->removeAllForFutureContainerEvents($container, $adherent, new \DateTime(), RegistrationStatusEnum::INVITED);

        if ($events = $this->eventRepository->findAllFutureInvitationEvents($container, new \DateTime())) {
            $this->algoliaManager->batch($events);
        }
    }

    private function resolveContainer(RemoveAdherentForAllFutureInvitationEventsCommand $command): Agora|Committee|null
    {
        if (null !== $command->agoraId) {
            return $this->agoraRepository->find($command->agoraId);
        }

        if (null !== $command->committeeId) {
            return $this->committeeRepository->find($command->committeeId);
        }

        return null;
    }
}
