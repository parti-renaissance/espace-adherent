<?php

namespace App\Normalizer\Indexer;

use App\Entity\Event\BaseEvent;
use App\Repository\Geo\ZoneRepository;

class EventNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    private ZoneRepository $zoneRepository;

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
    protected function getZoneCodes(object $object): ?array
    {
        $zone = $this->zoneRepository->findOneByPostalCode($object->getPostalCode());

        return $this->buildZoneCodes($zone ?? null);
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
