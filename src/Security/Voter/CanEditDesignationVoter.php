<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Scope\ScopeGeneratorResolver;

class CanEditDesignationVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_DESIGNATION';

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope || !$subject instanceof Designation) {
            return false;
        }

        return !$subject->isVotePeriodActive();
    }

    protected function supports(string $attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Designation;
    }
}
