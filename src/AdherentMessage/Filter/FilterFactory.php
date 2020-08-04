<?php

namespace App\AdherentMessage\Filter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;

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
        }
    }

    private static function createReferentFilter(Adherent $user): ReferentUserFilter
    {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        return new ReferentUserFilter($managedArea->getTags()->toArray());
    }

    private static function createDeputyFilter(Adherent $user): AdherentZoneFilter
    {
        if (!$user->isDeputy()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a deputy');
        }

        return new AdherentZoneFilter($user->getManagedDistrict()->getReferentTag());
    }

    private static function createCommitteeFilter(): CommitteeFilter
    {
        return new CommitteeFilter();
    }

    private static function createMunicipalChiefFilter(Adherent $adherent): MunicipalChiefFilter
    {
        return new MunicipalChiefFilter($adherent->getMunicipalChiefManagedArea()->getInseeCode());
    }

    private static function createSenatorFilter(Adherent $user): AdherentZoneFilter
    {
        return new AdherentZoneFilter($user->getSenatorArea()->getDepartmentTag());
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
}
