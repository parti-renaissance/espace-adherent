<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;

class CanManageActionVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_ACTION';

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    /** @param Action $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$scope->hasFeature(FeatureEnum::ACTIONS)) {
            return false;
        }

        return ($scope->getDelegator() ?? $adherent) === $subject->getAuthor();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Action;
    }
}
