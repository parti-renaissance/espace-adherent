<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;

class CanManageEventVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_EVENT';

    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($adherent === $subject->getAuthor()) {
            return true;
        }

        if ($scope = $this->scopeGeneratorResolver->generate()) {
            $delegatedAccess = $scope->getDelegatedAccess();

            return $subject->getAuthor() === $delegatedAccess->getDelegator()
                && $scope->hasFeature(FeatureEnum::EVENTS)
            ;
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof BaseEvent;
    }
}
