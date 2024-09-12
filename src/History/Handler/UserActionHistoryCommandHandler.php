<?php

namespace App\History\Handler;

use App\Entity\UserActionHistory;
use App\History\Command\UserActionHistoryCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserActionHistoryCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UserActionHistoryCommand $command): void
    {
        $history = $this->createUserActionHistoryFromCommand($command);

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    private function createUserActionHistoryFromCommand(UserActionHistoryCommand $command): UserActionHistory
    {
        return new UserActionHistory(
            $command->user,
            $command->type,
            $command->date,
            $command->data,
            $command->impersonificator
        );
    }
}
