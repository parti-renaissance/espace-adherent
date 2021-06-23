<?php

namespace App\Adherent\Handler;

use App\Adherent\AdherentInterestsMigrationHandler;
use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentMigrateInterestsCommandHandler implements MessageHandlerInterface
{
    private $adherentRepository;
    private $entityManager;
    private $migrationHandler;

    public function __construct(
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager,
        AdherentInterestsMigrationHandler $migrationHandler
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->migrationHandler = $migrationHandler;
    }

    public function __invoke(RemoveAdherentAndRelatedDataCommand $command): void
    {
        $adherent = $this->adherentRepository->findByUuid($command->getUuid()->toString());

        if (!$adherent) {
            return;
        }

        $this->entityManager->refresh($adherent);

        $this->migrationHandler->migrateInterests($adherent);

        $this->entityManager->flush();

        $this->entityManager->clear();
    }
}
