<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;

class ChangeJecouteNewsVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_CHANGE_JECOUTE_NEWS';
    public const LOCAL_SCOPES = [ScopeEnum::REFERENT, ScopeEnum::CORRESPONDENT];

    private ZoneRepository $zoneRepository;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(ZoneRepository $zoneRepository, ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->zoneRepository = $zoneRepository;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    /** @param News $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return false;
        }

        if (ScopeEnum::NATIONAL === $scope->getCode()) {
            return !$subject->getSpace();
        }

        $scopeCode = $scope->getDelegatorCode() ?? $scope->getCode();
        if (\in_array($scopeCode, self::LOCAL_SCOPES, true)) {
            if (!$zone = $subject->getZone()) {
                return false;
            }

            return $this->zoneRepository->isInZones([$zone], $scope->getZones());
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof News;
    }
}
