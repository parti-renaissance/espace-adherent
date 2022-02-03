<?php

namespace App\Security\Voter\Survey;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Geo\ManagedZoneProvider;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;

class CanReadSurveyVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_READ_SURVEY';

    private ManagedZoneProvider $managedZoneProvider;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        ManagedZoneProvider $managedZoneProvider,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->managedZoneProvider = $managedZoneProvider;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return false;
        }

        if ($scope->isNational()) {
            return $subject->isNational();
        }

        $scopeCode = $scope->getDelegatorCode() ?? $scope->getCode();
        if (ScopeEnum::REFERENT === $scopeCode) {
            if ($subject instanceof NationalSurvey) {
                return true;
            }

            if ($subject instanceof LocalSurvey) {
                return $this->managedZoneProvider->zoneBelongsToSomeZones($subject->getZone(), $scope->getZones());
            }
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Survey;
    }
}
