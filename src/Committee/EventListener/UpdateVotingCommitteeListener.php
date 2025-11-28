<?php

declare(strict_types=1);

namespace App\Committee\EventListener;

use App\Committee\CommitteeManager;
use App\Committee\Event\CommitteeMembershipEventInterface;
use App\Committee\Event\FollowCommitteeEvent;
use App\Committee\Event\UnfollowCommitteeEvent;
use App\Entity\CommitteeCandidacy;
use App\Membership\UserEvents;
use App\VotingPlatform\Election\Event\NewVote;
use App\VotingPlatform\Election\VotersListManager;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\CommitteeCandidacyEvent;
use App\VotingPlatform\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdateVotingCommitteeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly CommitteeManager $committeeManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly VotersListManager $votersListManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CANDIDACY_CREATED => 'onCandidacyCreated',
            NewVote::class => 'onVoteCreated',
            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE => 'onUpdateCommitteeMembership',
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
        if (!$event->election->getDesignation()->isCommitteeTypes()) {
            return;
        }

        if (!$committee = $event->election->getElectionEntity()->getCommittee()) {
            return;
        }

        if (!$adherent = $event->voter->getAdherent()) {
            return;
        }

        if (!$membership = $adherent->getMembershipFor($committee)) {
            return;
        }

        $this->committeeManager->enableVoteInMembership($membership, $adherent);
    }

    public function onUpdateCommitteeMembership(CommitteeMembershipEventInterface $event): void
    {
        if (!$this->authorizationChecker->isGranted('IS_IMPERSONATOR')) {
            return;
        }

        $membership = $event->getCommitteeMembership();

        if (!$committee = $membership->getCommittee()) {
            return;
        }

        $adherent = $membership->getAdherent();

        if ($event instanceof UnfollowCommitteeEvent) {
            $this->votersListManager->removeFromCommitteeElection($adherent, $committee);
        } elseif ($event instanceof FollowCommitteeEvent) {
            $this->votersListManager->addToCommitteeElection($adherent, $committee);
        }
    }
}
