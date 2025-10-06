<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Repository\Geo\ZoneRepository;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;

class PublicationVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_PUBLICATION';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ZoneRepository $zoneRepository,
    ) {
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

        if ($subject->isSent() || $subject->getInstanceScope() !== $scope->getMainCode()) {
            return false;
        }

        if (!($filter = $subject->getFilter()) instanceof AudienceFilter) {
            return false;
        }

        if (ScopeEnum::ANIMATOR === $subject->getInstanceScope()) {
            return \in_array($filter->getCommittee()?->getUuidAsString(), $scope->getCommitteeUuids());
        }

        return $this->zoneRepository->isInZones($filter->getZone() ? [$filter->getZone()] : $filter->getZones()->toArray(), $scope->getZones());
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof AdherentMessage;
    }
}
