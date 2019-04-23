<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;

abstract class FilterFactory
{
    public static function create(Adherent $user, string $messageType): AdherentMessageFilterInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                return static::createReferentFilter($user);
            case AdherentMessageTypeEnum::DEPUTY:
                return static::createDeputyFilter($user);
            case AdherentMessageTypeEnum::COMMITTEE:
                return static::createCommitteeFilter();
        }
    }

    private static function createReferentFilter(Adherent $user): AdherentMessageFilterInterface
    {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        $tags = $managedArea->getTags();

        return $tags->count() > 1 ? new AdherentZoneFilter() : new ReferentUserFilter($tags->first());
    }

    private static function createDeputyFilter(Adherent $user): AdherentMessageFilterInterface
    {
        if (!$user->isDeputy()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a deputy');
        }

        return new AdherentZoneFilter($user->getManagedDistrict()->getReferentTag());
    }

    private static function createCommitteeFilter(): AdherentMessageFilterInterface
    {
        return new CommitteeFilter();
    }
}
