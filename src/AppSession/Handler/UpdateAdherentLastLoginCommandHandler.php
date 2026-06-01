<?php

declare(strict_types=1);

namespace App\AppSession\Handler;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\AppSession\Command\UpdateAdherentLastLoginCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class UpdateAdherentLastLoginCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(UpdateAdherentLastLoginCommand $command): void
    {
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->adherentUuid)) {
            return;
        }

        $firstLogin = null === $adherent->getLastLoggedAt();

        $adherent->recordLastLoginTime($command->loginAt);

        $this->entityManager->flush();

        if ($firstLogin && $adherent->signupAccount) {
            $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($adherent->getUuid()));
        }
    }
}
