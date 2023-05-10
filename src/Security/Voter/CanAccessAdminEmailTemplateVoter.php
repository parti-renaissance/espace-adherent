<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\EmailTemplate\EmailTemplate;
use App\Scope\ScopeGeneratorResolver;

class CanAccessAdminEmailTemplateVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_ACCESS_ADMIN_TEMPLATE';

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope || !$subject instanceof EmailTemplate) {
            return false;
        }

        if (
            $subject->getCreatedByAdministrator()
            && \in_array($scope->getMainCode(), $subject->getScopes(), true)
            && ($subject->getZones()->isEmpty() || 0 !== \count(array_intersect($scope->getZones(), $subject->getZones()->toArray())))
        ) {
            return true;
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof EmailTemplate;
    }
}
