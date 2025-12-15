<?php

declare(strict_types=1);

namespace App\History\Handler;

use App\Entity\Administrator;
use App\Entity\AdministratorActionHistory;
use App\History\Command\AdministratorActionHistoryCommandInterface;
use App\Repository\AdministratorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AdministratorActionHistoryCommandHandler
{
    public function __construct(
        private readonly AdministratorRepository $administratorRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(AdministratorActionHistoryCommandInterface $command): void
    {
        $administrator = $this->administratorRepository->find($command->administratorId);

        if (!$administrator instanceof Administrator) {
            return;
        }

        $history = new AdministratorActionHistory(
            $administrator,
            $command->type,
            $command->date,
            $command->data
        );

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
