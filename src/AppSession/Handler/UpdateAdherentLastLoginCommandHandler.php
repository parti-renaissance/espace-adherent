<?php

declare(strict_types=1);

namespace App\AppSession\Handler;

use App\AppSession\Command\UpdateAdherentLastLoginCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateAdherentLastLoginCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateAdherentLastLoginCommand $command): void
    {
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->adherentUuid)) {
            return;
        }

        $adherent->recordLastLoginTime($command->loginAt);

        $this->entityManager->flush();
    }
}
