<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Committee;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Mailchimp\Synchronisation\Command\SyncAllMembersOfCommitteeCommand;
use App\Repository\CommitteeMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SyncAllMembersOfCommitteeCommandHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
        private readonly CommitteeMembershipRepository $repository,
    ) {
    }

    public function __invoke(SyncAllMembersOfCommitteeCommand $command): void
    {
        $adherents = $this->repository->findMembers($this->entityManager->getPartialReference(Committee::class, $command->getCommitteeId()));

        foreach ($adherents as $adherent) {
            $this->bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress(), batch: true));
        }
    }
}
