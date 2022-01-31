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
        if (ScopeEnum::NATIONAL === $scope->getCode()) {
            return !$subject->getSpace();
        }

        $scopeCode = $scope->getDelegatedAccess() ? $scope->getDelegatedAccess()->getType() : $scope->getCode();
        if (ScopeEnum::REFERENT === $scopeCode) {
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
