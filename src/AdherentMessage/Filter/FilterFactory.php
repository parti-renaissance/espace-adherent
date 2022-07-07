<?php

namespace App\AdherentMessage\Filter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\CoalitionsFilter;
use App\Entity\AdherentMessage\Filter\JecouteFilter;
use App\Entity\AdherentMessage\Filter\LreManagerElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\ReferentInstancesFilter;

abstract class FilterFactory
{
    public static function create(Adherent $user, string $messageType): AdherentMessageFilterInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                return static::createReferentFilter($user);
            case AdherentMessageTypeEnum::DEPUTY:
                return static::createDeputyFilter($user);
            case AdherentMessageTypeEnum::SENATOR:
                return static::createSenatorFilter($user);
            case AdherentMessageTypeEnum::COMMITTEE:
                return static::createCommitteeFilter();
            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                return static::createMunicipalChiefFilter($user);
            case AdherentMessageTypeEnum::REFERENT_ELECTED_REPRESENTATIVE:
                return static::createReferentElectedRepresentativeFilter($user);
            case AdherentMessageTypeEnum::LRE_MANAGER_ELECTED_REPRESENTATIVE:
                return static::createLreManagerElectedRepresentativeFilter($user);
            case AdherentMessageTypeEnum::REFERENT_INSTANCES:
                return static::createReferentTerritorialCouncilFilter($user);
            case AdherentMessageTypeEnum::CANDIDATE:
                return static::createCandidateFilter($user);
            case AdherentMessageTypeEnum::CANDIDATE_JECOUTE:
                return static::createCandidateJecouteFilter($user);
            case AdherentMessageTypeEnum::COALITIONS:
                return static::createCoalitionsFilter($user);
            case AdherentMessageTypeEnum::CORRESPONDENT:
                return static::createCorrespondentFilter($user);
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

    private static function createMunicipalChiefFilter(Adherent $adherent): MunicipalChiefFilter
    {
        return new MunicipalChiefFilter($adherent->getMunicipalChiefManagedArea()->getInseeCode());
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

    private static function createLreManagerElectedRepresentativeFilter(
        Adherent $user
    ): LreManagerElectedRepresentativeFilter {
        $lreArea = $user->getLreArea();

        if (!$lreArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a LRE Manager', $user->getEmailAddress()));
        }

        return new LreManagerElectedRepresentativeFilter($lreArea->getReferentTag());
    }

    private static function createReferentTerritorialCouncilFilter(Adherent $user): ReferentInstancesFilter
    {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        return new ReferentInstancesFilter();
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

    private static function createCoalitionsFilter(Adherent $user): CoalitionsFilter
    {
        if (!$user->isApprovedCauseAuthor()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a cause author');
        }

        return new CoalitionsFilter();
    }

    private static function createCorrespondentFilter(Adherent $user): AdherentGeoZoneFilter
    {
        if (!$user->isCorrespondent()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a correspondent');
        }

        return new AdherentGeoZoneFilter($user->getCorrespondentZone());
    }
}
