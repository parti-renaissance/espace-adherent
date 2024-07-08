<?php

namespace App\AdherentMessage\Filter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\JecouteFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Scope\Scope;

abstract class FilterFactory
{
    public static function create(
        Adherent $user,
        string $messageType,
        ?Scope $scope = null
    ): AdherentMessageFilterInterface {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                return static::createReferentFilter($user);
            case AdherentMessageTypeEnum::DEPUTY:
                return static::createDeputyFilter($user);
            case AdherentMessageTypeEnum::SENATOR:
                return static::createSenatorFilter($user);
            case AdherentMessageTypeEnum::COMMITTEE:
                return static::createCommitteeFilter();
            case AdherentMessageTypeEnum::REFERENT_ELECTED_REPRESENTATIVE:
                return static::createReferentElectedRepresentativeFilter($user);
            case AdherentMessageTypeEnum::CANDIDATE:
                return static::createCandidateFilter($user);
            case AdherentMessageTypeEnum::CANDIDATE_JECOUTE:
                return static::createCandidateJecouteFilter($user);
            case AdherentMessageTypeEnum::CORRESPONDENT:
                return static::createCorrespondentFilter($user);
            case AdherentMessageTypeEnum::REGIONAL_COORDINATOR:
                return static::createRegionalCoordinatorFilter($user);
            case AdherentMessageTypeEnum::STATUTORY:
                return static::createScopeZonesFilter($scope);
        }

        throw new \InvalidArgumentException(sprintf('Invalid message type "%s"', $messageType));
    }

    private static function createReferentFilter(Adherent $user): MessageFilter
    {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        return new MessageFilter($managedArea->getZones()->toArray());
    }

    private static function createDeputyFilter(Adherent $user): MessageFilter
    {
        if (!$user->isDeputy()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a deputy');
        }

        return new MessageFilter([$user->getDeputyZone()]);
    }

    private static function createCommitteeFilter(): MessageFilter
    {
        return new MessageFilter();
    }

    private static function createSenatorFilter(Adherent $user): MessageFilter
    {
        return new MessageFilter([$user->getSenatorArea()->getDepartmentTag()->getZone()]);
    }

    private static function createReferentElectedRepresentativeFilter(
        Adherent $user
    ): ReferentElectedRepresentativeFilter {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        return new ReferentElectedRepresentativeFilter($managedArea->getTags()->first());
    }

    private static function createCandidateFilter(Adherent $user): AdherentGeoZoneFilter
    {
        if (!$user->isCandidate()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a candidate');
        }

        return new AdherentGeoZoneFilter($user->getCandidateManagedArea()->getZone());
    }

    private static function createCandidateJecouteFilter(Adherent $user): JecouteFilter
    {
        if (!$user->isCandidate()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a candidate');
        }

        return new JecouteFilter($user->getCandidateManagedArea()->getZone());
    }

    private static function createCorrespondentFilter(Adherent $user): AdherentGeoZoneFilter
    {
        if (!$user->isCorrespondent()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a correspondent');
        }

        return new AdherentGeoZoneFilter($user->getCorrespondentZone());
    }

    private static function createRegionalCoordinatorFilter(Adherent $user): MessageFilter
    {
        if ($user->isRegionalCoordinator()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a regional coordinator');
        }

        return new MessageFilter($user->getRegionalCoordinatorZone());
    }

    private static function createScopeZonesFilter(?Scope $scope): MessageFilter
    {
        if (!$scope) {
            throw new \InvalidArgumentException('[AdherentMessage] Scope should not be empty');
        }

        return new MessageFilter($scope->getZones());
    }
}
