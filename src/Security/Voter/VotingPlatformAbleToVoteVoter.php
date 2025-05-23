<?php

namespace App\Security\Voter;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\VotingPlatform\Election;
use App\Repository\DonationRepository;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;

class VotingPlatformAbleToVoteVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_VOTE';
    public const PERMISSION_RESULTS = 'ABLE_TO_SEE_RESULTS';

    public function __construct(
        private readonly VoterRepository $voterRepository,
        private readonly VoteRepository $voteRepository,
        private readonly DonationRepository $donationRepository,
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var Election $subject */
        if (self::PERMISSION === $attribute) {
            if (!$subject->isVotePeriodActive()) {
                return false;
            }

            if ($this->voteRepository->alreadyVoted($adherent, $subject->getCurrentRound())) {
                return false;
            }
        }

        if ($adherent->isRenaissanceSympathizer()) {
            return false;
        }

        $designation = $subject->getDesignation();

        if ($designation->isCongressCNType()) {
            if (self::PERMISSION_RESULTS === $attribute) {
                return true;
            }

            if (
                ($designation->accountCreationDeadline && $adherent->getRegisteredAt() > $designation->accountCreationDeadline)
                || (
                    !$designation->accountCreationDeadline
                    && $designation->getElectionCreationDate()
                    && $adherent->getRegisteredAt() > $designation->getElectionCreationDate()
                )
            ) {
                return false;
            }

            if (!$this->donationRepository->countCotisationForAdherent($adherent, new \DateTime('2024-11-05 00:00:00'))) {
                return false;
            }

            if ($adherent->hasParliamentaryMandates()) {
                return $adherent->isContributionUpToDate();
            }

            return $adherent->hasActiveMembership();
        }

        if ($designation->isConsultationType() || $designation->isVoteType()) {
            if (
                ($designation->accountCreationDeadline && $adherent->getRegisteredAt() > $designation->accountCreationDeadline)
                || (
                    !$designation->accountCreationDeadline
                    && $designation->getElectionCreationDate()
                    && $adherent->getRegisteredAt() > $designation->getElectionCreationDate()
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
                if (!$foundTargetTag) {
                    return false;
                }
            } elseif (!$adherent->hasActiveMembership()) {
                return false;
            }

            if ($zones = $designation->getZones()->toArray()) {
                $managedZone = false;
                $zones = array_map(fn (Zone $zone) => $zone->getId(), $zones);

                foreach ($adherent->getDeepZones() as $zone) {
                    if (\in_array($zone->getId(), $zones, true)) {
                        $managedZone = true;
                        break;
                    }
                }

                if (!$managedZone) {
                    return false;
                }
            }
        }

        $adherentIsInVotersList = $this->voterRepository->existsForElection($adherent, $subject->getUuid()->toString());

        if (!$adherentIsInVotersList) {
            // Allow to vote adherent who are not on the list for CONSULTATION election
            if ($designation->isConsultationType() || $designation->isVoteType()) {
                return true;
            }

            if ($designation->isTerritorialAssemblyType() && $adherent->findActifLocalMandates()) {
                return true;
            }

            return false;
        }

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return (self::PERMISSION === $attribute || self::PERMISSION_RESULTS === $attribute) && $subject instanceof Election;
    }
}
