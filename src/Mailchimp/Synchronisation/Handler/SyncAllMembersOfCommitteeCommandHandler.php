<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Committee;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Mailchimp\Synchronisation\Command\SyncAllMembersOfCommitteeCommand;
use App\Repository\CommitteeMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SyncAllMembersOfCommitteeCommandHandler implements MessageHandlerInterface
{
    private $bus;
    private $entityManager;
    private $repository;

    public function __construct(
        MessageBusInterface $bus,
        EntityManagerInterface $entityManager,
        CommitteeMembershipRepository $repository,
    ) {
        $this->bus = $bus;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function __invoke(SyncAllMembersOfCommitteeCommand $command): void
    {
        $adherents = $this->repository->findMembers($this->entityManager->getPartialReference(Committee::class, $command->getCommitteeId()));

        foreach ($adherents as $adherent) {
            $this->bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress()));
        }
    }
}
