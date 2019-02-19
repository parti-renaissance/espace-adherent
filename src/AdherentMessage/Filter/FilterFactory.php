<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;

abstract class FilterFactory
{
    public static function create(Adherent $user, string $messageType): AdherentMessageFilterInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                return static::createReferentFilter($user);
            case AdherentMessageTypeEnum::DEPUTY:
                return static::createDeputyFilter($user);
        }
    }

    private static function createReferentFilter(Adherent $user): AdherentMessageFilterInterface
    {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        $validTags = [];

        foreach ($referentTags = $managedArea->getTags() as $tag) {
            if ($tag->getExternalId()) {
                $validTags[] = $tag;
            }
        }

        if ($count = \count($validTags)) {
            return $count > 1 ? new AdherentZoneFilter($validTags) : new ReferentUserFilter(current($validTags));
        }

        throw new \RuntimeException(sprintf('[AdherentMessage] The current referent "%s" does not have a valid referent tag for creating a filter', $user->getEmailAddress()));
    }

    private static function createDeputyFilter(Adherent $user): AdherentMessageFilterInterface
    {
        if (!$user->isDeputy()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a deputy');
        }

        return new AdherentZoneFilter([$user->getManagedDistrict()->getReferentTag()]);
    }
}
