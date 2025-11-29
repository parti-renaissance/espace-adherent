<?php

declare(strict_types=1);

namespace App\VotingPlatform\Security;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class ElectionAuthorisationChecker
{
    public function __construct(
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly ElectionRepository $electionRepository,
        private readonly VoterRepository $voterRepository,
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
                $adherent->isDeputy()
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
            ($candidateMembership = $adherent->getCommitteeMembership())
            && $candidateMembership->hasActiveCommitteeCandidacy(true)
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
        if (!($membership = $adherent->getMembershipFor($committee)) || !($election = $committee->getCurrentElection())) {
            return false;
        }

        $refDate = $election->getVoteEndDate() ?? new \DateTime();

        if ($adherent->isMinor($refDate)) {
            return false;
        }

        if (!$adherent->isCertified()) {
            return false;
        }

        if (
            ($candidateMembership = $adherent->getCommitteeMembership())
            && $candidateMembership->hasActiveCommitteeCandidacy(true)
            && !$candidateMembership->getCommittee()->equals($committee)
        ) {
            return false;
        }

        $designation = $election->getDesignation();

        if (($committeeRecentVote = $this->committeeRepository->findCommitteeForRecentVote($designation, $adherent)) && !$committeeRecentVote->equals($committee)) {
            return false;
        }

        if (($committeeRecentCandidate = $this->committeeRepository->findCommitteeForRecentCandidate($designation, $adherent)) && !$committeeRecentCandidate->equals($committee)) {
            return false;
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            if ($membership->getSubscriptionDate()->modify('30 days') > $refDate) {
                return false;
            }

            if (!($registrationDate = $adherent->getRegisteredAt()) || (clone $registrationDate)->modify('+3 months') > $refDate) {
                return false;
            }
        }

        return true;
    }

    public function isVoterOnCommittee(Committee $committee, Adherent $adherent): bool
    {
        if (!$designation = $committee->getCurrentDesignation()) {
            return false;
        }

        if (!$election = $this->electionRepository->findOneForCommittee($committee, $designation)) {
            return false;
        }

        return $this->voterRepository->existsForElection($adherent, $election->getUuid()->toString());
    }
}
