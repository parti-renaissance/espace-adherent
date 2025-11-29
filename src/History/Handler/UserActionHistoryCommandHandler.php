<?php

declare(strict_types=1);

namespace App\History\Handler;

use App\Entity\Adherent;
use App\Entity\UserActionHistory;
use App\History\Command\UserActionHistoryCommand;
use App\Repository\AdherentRepository;
use App\Repository\AdministratorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserActionHistoryCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly AdministratorRepository $administratorRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UserActionHistoryCommand $command): void
    {
        $adherent = $this->adherentRepository->findOneByUuid($command->adherentUuid);

        if (!$adherent instanceof Adherent) {
            return;
        }

        $history = new UserActionHistory(
            $adherent,
            $command->type,
            $command->date,
            $command->data,
            $command->administratorId ? $this->administratorRepository->find($command->administratorId) : null
        );

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
