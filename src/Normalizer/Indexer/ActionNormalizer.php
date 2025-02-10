<?php

namespace App\Normalizer\Indexer;

use App\Entity\Action\Action;
use App\Entity\Adherent;

class ActionNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return Action::class;
    }

    /** @param Action $object */
    protected function getTitle(object $object): string
    {
        return '';
    }

    /** @param Action $object */
    protected function getDescription(object $object): ?string
    {
        return $object->description;
    }

    /** @param Action $object */
    protected function getCategory(object $object): ?string
    {
        return ucfirst($object->type);
    }

    /** @param Action $object */
    protected function getPostAddress(object $object): ?array
    {
        return $object->getPostAddress()?->toArray();
    }

    /** @param Action $object */
    protected function getBeginAt(object $object): ?\DateTime
    {
        return $object->date;
    }

    /** @param Action $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param Action $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->getAuthor();
    }

    /** @param Action $object */
    protected function getZoneCodes(object $object): ?array
    {
        if ($object->getZones()->isEmpty()) {
            return null;
        }

        $zonesCodes = [];

        foreach ($object->getZones() as $zone) {
            $zonesCodes[] = $this->buildZoneCodes($zone);
        }

        return array_values(array_unique(array_merge(...$zonesCodes)));
    }

    /** @param Action $object */
    protected function getParticipantsCount(object $object): ?int
    {
        return $object->getParticipantsCount();
    }
}
