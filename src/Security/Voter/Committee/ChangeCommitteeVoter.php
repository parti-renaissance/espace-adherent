<?php

declare(strict_types=1);

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Repository\VotingPlatform\VoterRepository;
use App\Security\Voter\AbstractAdherentVoter;

class ChangeCommitteeVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_CHANGE_COMMITTEE';

    public function __construct(private readonly VoterRepository $voterRepository)
    {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($adherent->isForeignResident()) {
            return false;
        }

        if ($this->voterRepository->isInVoterListForCommitteeElection($adherent)) {
            return false;
        }

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute;
    }
}
