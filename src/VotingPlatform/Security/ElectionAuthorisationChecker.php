<?php

namespace App\VotingPlatform\Security;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class ElectionAuthorisationChecker
{
    public function __construct(
        public readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
        public readonly CommitteeRepository $committeeRepository,
        public readonly VoterRepository $voterRepository
    ) {
    }

    public function canCandidateOnCommittee(Committee $committee, Adherent $adherent): bool
    {
        if (!($membership = $adherent->getMembershipFor($committee)) || !($election = $committee->getCurrentElection())) {
            return false;
        }

        if (!$election->isCandidacyPeriodActive()) {
            return false;
        }

        $designation = $election->getDesignation();

        if ($designation->getPools() && !\in_array($adherent->getGender(), $designation->getPools(), true)) {
            return false;
        }

        $refDate = $election->getVoteEndDate() ?? new \DateTime();

        if ($adherent->isMinor($refDate)) {
            return false;
        }

        if (!$adherent->isCertified()) {
            return false;
        }

        if ($adherent->isSupervisor(false)) {
            return false;
        }

        if ($this->electedRepresentativeRepository->hasActiveParliamentaryMandate($adherent)) {
            return false;
        }

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $election->getDesignationType()) {
            if (
                $adherent->isReferent()
                || $adherent->isDeputy()
                || $adherent->isSenator()
                || $adherent->getActiveDesignatedAdherentMandates()
            ) {
                return false;
            }
        } else {
            if ($membership->getSubscriptionDate()->modify('30 days') > $refDate) {
                return false;
            }

            if (!($registrationDate = $adherent->getRegisteredAt()) || (clone $registrationDate)->modify('+3 months') > $refDate) {
                return false;
            }
        }

        if (
            ($candidateMembership = $adherent->getMemberships()->getCommitteeCandidacyMembership(true))
            && !$candidateMembership->getCommittee()->equals($committee)
        ) {
            return false;
        }

        if (($committeeRecentVote = $this->committeeRepository->findCommitteeForRecentVote($designation, $adherent)) && !$committeeRecentVote->equals($committee)) {
            return false;
        }

        if (($committeeRecentCandidate = $this->committeeRepository->findCommitteeForRecentCandidate($designation, $adherent)) && !$committeeRecentCandidate->equals($committee)) {
            return false;
        }

        return true;
    }

    public function canVoteOnCommittee(Committee $committee, Adherent $adherent): bool
    {
        if (!($election = $committee->getCurrentElection())) {
            return false;
        }

        return $this->voterRepository->existsForElection($adherent, $election->getUuid()->toString());
    }
}
