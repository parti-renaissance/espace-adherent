<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeManager;
use App\Entity\CommitteeCandidacy;
use App\VotingPlatform\Election\Event\NewVote;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\CommitteeCandidacyEvent;
use App\VotingPlatform\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateVotingCommitteeOnCandidateListener implements EventSubscriberInterface
{
    private $committeeManager;

    public function __construct(CommitteeManager $committeeManager)
    {
        $this->committeeManager = $committeeManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CANDIDACY_CREATED => 'onCandidacyCreated',
            NewVote::class => 'onVoteCreated',
        ];
    }

    public function onCandidacyCreated(BaseCandidacyEvent $event): void
    {
        if (!$event instanceof CommitteeCandidacyEvent) {
            return;
        }

        /** @var CommitteeCandidacy $candidacy */
        $candidacy = $event->getCandidacy();

        $this->committeeManager->enableVoteInMembership(
            $candidacy->getCommitteeMembership(),
            $candidacy->getAdherent()
        );
    }

    public function onVoteCreated(NewVote $event): void
    {
        if (!$committee = $event->getElection()->getElectionEntity()->getCommittee()) {
            return;
        }

        if (!$adherent = $event->getVoter()->getAdherent()) {
            return;
        }

        if (!$membership = $adherent->getMembershipFor($committee)) {
            return;
        }

        $this->committeeManager->enableVoteInMembership($membership, $adherent);
    }
}
