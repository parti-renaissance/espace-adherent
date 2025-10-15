<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;

class PublicationVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_PUBLICATION';

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    /** @param AdherentMessage $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$scope->hasFeature(FeatureEnum::MESSAGES) && !$scope->hasFeature(FeatureEnum::PUBLICATIONS)) {
            return false;
        }

        if ($subject->getAuthor() === $adherent) {
            return true;
        }

        return !$subject->isSent() && $subject->teamOwner === $scope->getMainUser();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof AdherentMessage;
    }
}
