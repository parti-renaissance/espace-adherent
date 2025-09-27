<?php

namespace App\Security\Voter;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;

class VotingPlatformAbleToVoteVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_VOTE';
    public const POTENTIAL_PERMISSION = 'POTENTIALLY_ABLE_TO_VOTE';
    public const PERMISSION_RESULTS = 'ABLE_TO_SEE_RESULTS';

    public function __construct(
        private readonly VoterRepository $voterRepository,
        private readonly VoteRepository $voteRepository,
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var Election $subject */
        if (\in_array($attribute, [self::PERMISSION, self::POTENTIAL_PERMISSION])) {
            if (!$subject->isVotePeriodActive()) {
                return false;
            }

            if ($this->voteRepository->alreadyVoted($adherent, $subject->getCurrentRound())) {
                return false;
            }
        }

        if (!$designation = $subject->getDesignation()) {
            return false;
        }

        if (
            $adherent->isRenaissanceSympathizer()
            && (
                self::PERMISSION === $attribute
                || (self::POTENTIAL_PERMISSION === $attribute && $designation->membershipDeadline && $designation->membershipDeadline < new \DateTime())
            )
        ) {
            return false;
        }

        if ($designation->accountCreationDeadline && $adherent->getRegisteredAt() > $designation->accountCreationDeadline) {
            return false;
        }

        if (
            !$designation->accountCreationDeadline
            && $designation->getElectionCreationDate()
            && ($designation->isConsultationType() || $designation->isVoteType() || $designation->isCongressCNType())
            && $adherent->getRegisteredAt() > $designation->getElectionCreationDate()
        ) {
            return false;
        }

        if (
            $designation->membershipDeadline
            && (
                $adherent->getLastMembershipDonation() > $designation->membershipDeadline
                || new \DateTime() > $designation->membershipDeadline
            )
        ) {
            return false;
        }

        if ($designation->targetYear) {
            $foundTargetTag = false;
            foreach (range($designation->targetYear, date('Y')) as $year) {
                if ($adherent->hasTag(TagEnum::getAdherentYearTag($year))) {
                    $foundTargetTag = true;
                    break;
                }
            }

            if (!$foundTargetTag && self::PERMISSION === $attribute) {
                return false;
            }
        }

        if ($zones = $designation->getZones()->toArray()) {
            $isUserInsideZone = false;
            $zones = array_map(static fn (Zone $zone) => $zone->getId(), $zones);

            foreach ($adherent->getDeepZones() as $zone) {
                if (\in_array($zone->getId(), $zones, true)) {
                    $isUserInsideZone = true;
                    break;
                }
            }

            if (!$isUserInsideZone) {
                return false;
            }
        }

        if ($designation->isTerritorialAssemblyType() && $adherent->findActifLocalMandates()) {
            return true;
        }

        if (
            $designation->isCommitteeTypes()
            && (
                !$adherent->isRenaissanceAdherent()
                || !($committeeMembership = $adherent->getCommitteeMembership())
                || $committeeMembership->getCommittee() !== $subject->getElectionEntity()?->getCommittee()
                || ($designation->getElectionCreationDate() && $committeeMembership->getJoinedAt() > $designation->getElectionCreationDate())
            )
        ) {
            return false;
        }

        if ($designation->isCongressCNType()) {
            if (self::PERMISSION_RESULTS === $attribute) {
                return true;
            }

            if ($adherent->hasParliamentaryMandates()) {
                return $adherent->isContributionUpToDate() || self::POTENTIAL_PERMISSION === $attribute;
            }

            return $adherent->hasActiveMembership() || self::POTENTIAL_PERMISSION === $attribute;
        }

        if (
            $designation->isConsultationType()
            || $designation->isVoteType()
            || $designation->isTerritorialAnimatorType()
        ) {
            return $designation->targetYear || $adherent->hasActiveMembership() || self::POTENTIAL_PERMISSION === $attribute;
        }

        return $this->voterRepository->existsForElection($adherent, $subject->getUuid()->toString());
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::PERMISSION_RESULTS, self::PERMISSION, self::POTENTIAL_PERMISSION]) && $subject instanceof Election;
    }
}
