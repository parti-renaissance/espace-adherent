<?php

declare(strict_types=1);

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Repository\Geo\ZoneRepository;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeEnum;
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
            return $this->canManageEvent($adherent, $subject);
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

    private function canManageEvent(Adherent $adherent, Event $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return $this->canManageEventItem($adherent, [
                'instance_key' => $subject->getInstanceKey(),
                'instance' => $subject->getAuthorInstance(),
                'zones' => $subject->getZones()->toArray(),
                'committee_uuid' => $subject->getCommitteeUuid(),
                'agora_uuid' => $subject->agora?->getUuid()->toRfc4122(),
                'is_national' => $subject->isNational(),
                'author_uuid' => $subject->getAuthor()?->getUuidAsString(),
                'author_scope' => $subject->getAuthorScope(),
            ]);
        }

        if (!$scope->hasFeature(FeatureEnum::EVENTS)) {
            return false;
        }

        if ($scope->isNational()) {
            return true;
        }

        // A militant manages only their own militant events (the scope is never delegated).
        if (ScopeEnum::MILITANT === $scope->getMainCode() && ScopeEnum::MILITANT === $subject->getAuthorScope()) {
            return $adherent === $subject->getAuthor();
        }

        if ($subject->getInstanceKey()) {
            return $subject->getInstanceKey() === $scope->getInstanceKey();
        }

        if ($subject->getAuthorInstance() !== $scope->getScopeInstance()) {
            return false;
        }

        if ($subject->getCommittee()) {
            return \in_array($subject->getCommitteeUuid(), $scope->getCommitteeUuids());
        }

        if ($subject->agora) {
            return \in_array($subject->agora->getUuid()->toRfc4122(), $scope->getAgoraUuids());
        }

        return $this->zoneRepository->isInZones($subject->getZones()->toArray(), $scope->getZones());
    }

    private function canManageEventItem(Adherent $adherent, array $event): bool
    {
        try {
            $scopes = $this->generalScopeGenerator->generateScopes($adherent);
        } catch (ScopeExceptionInterface $e) {
            return false;
        }

        if (
            ScopeEnum::MILITANT === ($event['author_scope'] ?? null)
            && !empty($event['author_uuid'])
            && $event['author_uuid'] === $adherent->getUuidAsString()
            && array_any($scopes, static fn (Scope $scope) => ScopeEnum::MILITANT === $scope->getMainCode() && $scope->hasFeature(FeatureEnum::EVENTS))
        ) {
            return true;
        }

        $scopes = array_filter(
            $scopes,
            static function (Scope $scope) use ($event) {
                if ($scope->isNational() && $scope->hasFeature(FeatureEnum::EVENTS)) {
                    return true;
                }

                if (!empty($event['instance_key'])) {
                    return $scope->getInstanceKey() === $event['instance_key'] && $scope->hasFeature(FeatureEnum::EVENTS);
                }

                return $scope->getScopeInstance() === $event['instance'] && $scope->hasFeature(FeatureEnum::EVENTS);
            }
        );

        if (empty($scopes)) {
            return false;
        }

        if (array_any($scopes, static fn (Scope $scope) => $scope->isNational())) {
            return true;
        }

        foreach ($scopes as $scope) {
            if (!empty($event['instance_key'])) {
                return $event['instance_key'] === $scope->getInstanceKey();
            }

            if (!empty($event['committee_uuid'])) {
                if (\in_array($event['committee_uuid'], $scope->getCommitteeUuids())) {
                    return true;
                }
            } elseif (!empty($event['agora_uuid'])) {
                if (\in_array($event['agora_uuid'], $scope->getAgoraUuids())) {
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
