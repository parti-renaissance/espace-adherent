<?php

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Repository\Geo\ZoneRepository;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;

class CanManageEventVoter extends AbstractAdherentVoter
{
    public const CAN_MANAGE_EVENT = 'CAN_MANAGE_EVENT';
    public const CAN_MANAGE_EVENT_ITEM = 'CAN_MANAGE_EVENT_ITEM';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    /** @param Event $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (self::CAN_MANAGE_EVENT === $attribute) {
            return $this->canManageEvent($subject);
        }

        if (self::CAN_MANAGE_EVENT_ITEM === $attribute) {
            return $this->canManageEventItem($adherent, $subject);
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return (self::CAN_MANAGE_EVENT === $attribute && $subject instanceof Event)
            || (self::CAN_MANAGE_EVENT_ITEM === $attribute && \is_array($subject));
    }

    private function canManageEvent(Event $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$scope->hasFeature(FeatureEnum::EVENTS)) {
            return false;
        }

        if ($subject->getAuthorInstance() !== $scope->getScopeInstance()) {
            return false;
        }

        if ($subject->getCommittee()) {
            return \in_array($subject->getCommitteeUuid(), $scope->getCommitteeUuids());
        }

        return $this->zoneRepository->isInZones($subject->getZones()->toArray(), $scope->getZones());
    }

    private function canManageEventItem(Adherent $adherent, array $event): bool
    {
        try {
            $scopes = array_filter(
                $this->generalScopeGenerator->generateScopes($adherent),
                fn (Scope $scope) => $scope->getScopeInstance() === $event['instance'] && $scope->hasFeature(FeatureEnum::EVENTS)
            );
        } catch (ScopeExceptionInterface $e) {
            return false;
        }

        if (empty($scopes)) {
            return false;
        }

        foreach ($scopes as $scope) {
            if (!empty($event['committee_uuid'])) {
                if (\in_array($event['committee_uuid'], $scope->getCommitteeUuids())) {
                    return true;
                }
            } else {
                if ($this->zoneRepository->isInZonesUsingCodes($event['zones'], $scope->getZones())) {
                    return true;
                }
            }
        }

        return false;
    }
}
