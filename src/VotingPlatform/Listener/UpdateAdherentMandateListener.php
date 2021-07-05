<?php

namespace App\VotingPlatform\Listener;

use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Command\UpdateMandateForElectedAdherentCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateAdherentMandateListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            VotingPlatformElectionVoteIsOverEvent::class => ['onVoteClose', -256],
        ];
    }

    public function onVoteClose(VotingPlatformElectionVoteIsOverEvent $event): void
    {
        if (!$this->validateElection($event->getElection())) {
            return;
        }

        $this->bus->dispatch(new UpdateMandateForElectedAdherentCommand($event->getElection()->getUuid()));
    }

    private function validateElection(Election $election): bool
    {
        return \in_array($election->getDesignationType(), [
            DesignationTypeEnum::COMMITTEE_ADHERENT,
            DesignationTypeEnum::COMMITTEE_SUPERVISOR,
            DesignationTypeEnum::COPOL,
            DesignationTypeEnum::NATIONAL_COUNCIL,
        ], true);
    }
}
