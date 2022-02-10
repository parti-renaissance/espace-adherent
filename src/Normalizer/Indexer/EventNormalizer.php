<?php

namespace App\Normalizer\Indexer;

use App\Entity\Event\BaseEvent;
use App\Entity\Geo\Zone;
use App\Storage\UrlAdapterInterface;
use League\Flysystem\FilesystemInterface;

class EventNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    private UrlAdapterInterface $storage;

    protected function getClassName(): string
    {
        return BaseEvent::class;
    }

    /** @param BaseEvent $object */
    protected function getTitle(object $object): string
    {
        return $object->getName();
    }

    /** @param BaseEvent $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getDescription();
    }

    /** @param BaseEvent $object */
    protected function isLocal(object $object): bool
    {
        return true;
    }

    /** @param BaseEvent $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param BaseEvent $object */
    protected function getAuthor(object $object): ?string
    {
        return $object->getAuthor() ? $object->getAuthor()->getFullName() : null;
    }

    /** @param BaseEvent $object */
    protected function getCategory(object $object): ?string
    {
        return $object->getCategoryName();
    }

    /** @param BaseEvent $object */
    protected function getAddress(object $object): ?string
    {
        return $object->getInlineFormattedAddress();
    }

    /** @param BaseEvent $object */
    protected function getBeginAt(object $object): ?\DateTime
    {
        return $object->getFinishAt();
    }

    /** @param BaseEvent $object */
    protected function getFinishAt(object $object): ?\DateTime
    {
        return $object->getFinishAt();
    }

    /** @param BaseEvent $object */
    protected function getImage(object $object): ?string
    {
        return $object->hasImageName() ? $this->storage->getUrl($object->getImagePath()) : null;
    }

    /** @param BaseEvent $object */
    protected function getZoneCodes(object $object): ?array
    {
        if ($object->getZones()->isEmpty()) {
            return null;
        }

        $zonesCodes = [];
        $zones = array_filter($object->zones->toArray(), function (Zone $zone) {
            return \in_array($zone->getType(), [Zone::BOROUGH, Zone::CITY]);
        });

        foreach ($zones as $key => $zone) {
            $zonesCodes[$key] = $this->buildZoneCodes($zone);
        }

        return array_unique(array_merge(...$zonesCodes));
    }

    /** @required */
    public function setStorage(FilesystemInterface $storage): void
    {
        $this->storage = $storage->getAdapter();
    }
}
