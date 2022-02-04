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
    public const LOCAL_SCOPES = [ScopeEnum::REFERENT, ScopeEnum::CORRESPONDENT];

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
        if (\in_array($scopeCode, self::LOCAL_SCOPES, true)) {
            if ($subject instanceof NationalSurvey) {
                return true;
            }

            if ($subject instanceof LocalSurvey) {
                return $this->managedZoneProvider->zoneBelongsToSomeZones($subject->getZone(), $scope->getZones())
                    || ($adherent->isCorrespondent() && $subject->getZone() === $adherent->getCorrespondentZone())
                ;
            }
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Survey;
    }
}
