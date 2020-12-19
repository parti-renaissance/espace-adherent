<?php

namespace App\VotingPlatform\Security;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class ElectionAuthorisationChecker
{
    private $electedRepresentativeRepository;

    public function __construct(ElectedRepresentativeRepository $electedRepresentativeRepository)
    {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
    }

    public function canCandidateOnCommittee(Committee $committee, Adherent $adherent): bool
    {
        if (!($membership = $adherent->getMembershipFor($committee)) || !($election = $committee->getCurrentElection())) {
            return false;
        }

        $refDate = $election->getVoteEndDate() ?? new \DateTime();

        if (
            ($candidateMembership = $adherent->getMemberships()->getCommitteeCandidacyMembership())
            && !$candidateMembership->getCommittee()->equals($committee)
        ) {
            return false;
        }

        if ($adherent->isMinor($refDate)) {
            return false;
        }

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $election->getDesignationType()) {
            if (
                $adherent->isReferent()
                || $adherent->isSupervisor()
                || $adherent->isDeputy()
                || $adherent->isSenator()
            ) {
                return false;
            }

            return true;
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            if (!$adherent->isCertified()) {
                return false;
            }

            if ($membership->getSubscriptionDate()->modify('+1 months') > $refDate) {
                return false;
            }

            if (!$registrationDate = $adherent->getRegisteredAt()) {
                return false;
            }

            if ((clone $registrationDate)->modify('+3 months') > $refDate) {
                return false;
            }

            if ($this->electedRepresentativeRepository->hasActiveParliamentaryMandate($adherent)) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function canVoteOnCommittee(Committee $committee, Adherent $adherent): bool
    {
        if (!($membership = $adherent->getMembershipFor($committee)) || !($election = $committee->getCurrentElection())) {
            return false;
        }

        $refDate = $election->getVoteEndDate() ?? new \DateTime();

        if (
            ($candidateMembership = $adherent->getMemberships()->getCommitteeCandidacyMembership())
            && !$candidateMembership->getCommittee()->equals($committee)
        ) {
            return false;
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            if (!$adherent->isCertified()) {
                return false;
            }

            if ($membership->getSubscriptionDate()->modify('+1 months') > $refDate) {
                return false;
            }

            if (!$registrationDate = $adherent->getRegisteredAt()) {
                return false;
            }

            if ((clone $registrationDate)->modify('+3 months') > $refDate) {
                return false;
            }
        }

        return true;
    }
}
