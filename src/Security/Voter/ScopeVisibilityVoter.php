<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityInterface;
use App\Geo\ManagedZoneProvider;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;

class ScopeVisibilityVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'SCOPE_CAN_MANAGE';

    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private ManagedZoneProvider $managedZoneProvider;

    public function __construct(
        Security $security,
        ScopeGeneratorResolver $scopeGeneratorResolver,
        ManagedZoneProvider $managedZoneProvider
    ) {
        $this->security = $security;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->managedZoneProvider = $managedZoneProvider;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return false;
        }

        if ($scope->isNational()) {
            return null === $subject->getZone();
        }

        if (null === $subject->getZone()) {
            return false;
        }

        return $this->managedZoneProvider->zoneBelongsToSomeZones($subject->getZone(), $scope->getZones());
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof EntityScopeVisibilityInterface;
    }
}
