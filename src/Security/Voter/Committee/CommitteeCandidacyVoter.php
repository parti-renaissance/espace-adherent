<?php

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class CommitteeCandidacyVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_CANDIDATE';

    /**
     * @param Committee $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!($membership = $adherent->getMembershipFor($subject)) || !($election = $subject->getCurrentElection())) {
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

            $refDate = $election->getVoteEndDate() ?? new \DateTime();

            if ($membership->getSubscriptionDate()->modify('+1 months') > $refDate) {
                return false;
            }

            if (!$registrationDate = $adherent->getRegisteredAt()) {
                return false;
            }

            if ((clone $registrationDate)->modify('+3 months') > $refDate) {
                return false;
            }

            return true;
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Committee;
    }
}
