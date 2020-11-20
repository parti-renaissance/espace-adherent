<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeManager;
use App\Entity\CommitteeCandidacy;
use App\VotingPlatform\Designation\DesignationTypeEnum;
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
        ];
    }

    public function onCandidacyCreated(BaseCandidacyEvent $event): void
    {
        if (!$event instanceof CommitteeCandidacyEvent || DesignationTypeEnum::COMMITTEE_SUPERVISOR !== $event->getCandidacy()->getType()) {
            return;
        }

        /** @var CommitteeCandidacy $candidacy */
        $candidacy = $event->getCandidacy();

        $this->committeeManager->enableVoteInMembership(
            $candidacy->getCommitteeMembership(),
            $candidacy->getAdherent()
        );
    }
}
