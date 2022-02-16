<?php

namespace App\Normalizer\Indexer;

use App\Entity\Geo\Zone;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;

abstract class AbstractJeMengageTimelineFeedNormalizer extends AbstractIndexerNormalizer
{
    final public function normalize($object, $format = null, array $context = [])
    {
        return [
            'type' => $this->getType(),
            'is_local' => $this->isLocal($object),
            'title' => $this->getTitle($object),
            'description' => $this->getDescription($object),
            'category' => $this->getCategory($object),
            'address' => $this->getAddress($object),
            'image' => $this->getImage($object),
            'begin_at' => $this->formatDate($this->getBeginAt($object)),
            'finish_at' => $this->formatDate($this->getFinishAt($object)),
            'date' => $this->formatDate($this->getDate($object)),
            'time_zone' => $this->getTimeZone($object),
            'author' => $this->getAuthor($object),
            'is_national' => $this->isNational($object),
            'zone_codes' => $this->getZoneCodes($object),
            'adherent_ids' => $this->getAdherentIds($object),
            '_tags' => [$this->getType()],
        ];
    }

    abstract protected function getTitle(object $object): string;

    abstract protected function getDescription(object $object): ?string;

    abstract protected function isLocal(object $object): bool;

    abstract protected function getDate(object $object): ?\DateTime;

    abstract protected function getAuthor(object $object): ?string;

    final private function getType(): string
    {
        return TimelineFeedTypeEnum::CLASS_MAPPING[$this->getClassName()];
    }

    protected function getCategory(object $object): ?string
    {
        return null;
    }

    protected function getAddress(object $object): ?string
    {
        return null;
    }

    protected function getBeginAt(object $object): ?\DateTime
    {
        return null;
    }

    protected function getFinishAt(object $object): ?\DateTime
    {
        return null;
    }

    protected function isNational(object $object): bool
    {
        return false;
    }

    protected function getTimeZone(object $object): ?string
    {
        return 'Europe/Paris';
    }

    protected function getImage(object $object): ?string
    {
        return null;
    }

    protected function getZoneCodes(object $object): ?array
    {
        return null;
    }

    protected function getAdherentIds(object $object): ?array
    {
        return null;
    }

    final protected function buildZoneCodes(?Zone $zone): ?array
    {
        if (!$zone) {
            return null;
        }

        $codes = [sprintf('%s_%s', $zone->getType(), $zone->getCode())];

        foreach ($zone->getParents() as $parentZone) {
            $codes[] = sprintf('%s_%s', $parentZone->getType(), $parentZone->getCode());
        }

        return $codes;
    }
}
