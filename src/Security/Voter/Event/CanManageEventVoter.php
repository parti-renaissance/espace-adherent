<?php

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;

class CanManageEventVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_EVENT';

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$scope->hasFeature(FeatureEnum::EVENTS)) {
            return false;
        }

        return ($scope->getDelegator() ?? $adherent) === $subject->getAuthor();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof BaseEvent;
    }
}
