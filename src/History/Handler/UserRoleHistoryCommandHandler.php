<?php

namespace App\History\Handler;

use App\Entity\Adherent;
use App\Entity\Reporting\UserRoleHistory;
use App\History\Command\UserRoleHistoryCommand;
use App\Repository\AdherentRepository;
use App\Repository\AdministratorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserRoleHistoryCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly AdministratorRepository $administratorRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UserRoleHistoryCommand $command): void
    {
        $user = $this->adherentRepository->findOneByUuid($command->userUuid);

        if (!$user instanceof Adherent) {
            return;
        }

        $history = new UserRoleHistory(
            $user,
            $command->action,
            $command->role,
            $command->zones,
            $command->adminAuthorId ? $this->administratorRepository->find($command->adminAuthorId) : null,
            $command->userAuthorUuid ? $this->adherentRepository->findOneByUuid($command->userAuthorUuid) : null,
        );

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
