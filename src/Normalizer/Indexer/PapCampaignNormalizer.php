<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Pap\Campaign;

class PapCampaignNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return Campaign::class;
    }

    /** @param Campaign $object */
    protected function getTitle(object $object): string
    {
        return $object->getTitle();
    }

    /** @param Campaign $object */
    protected function getDescription(object $object): ?string
    {
        return 'Nouvelle campagne de porte-à-porte autour de vous.';
    }

    /** @param Campaign $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->getCreatedAt();
    }

    /** @param Campaign $object */
    protected function getFinishAt(object $object): ?\DateTimeInterface
    {
        return $object->getFinishAt();
    }

    /** @param Campaign $object */
    protected function getBeginAt(object $object): ?\DateTimeInterface
    {
        return $object->getBeginAt();
    }

    /** @param Campaign $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return null;
    }

    /** @param Campaign $object */
    protected function isNational(object $object): bool
    {
        return $object->isNationalVisibility();
    }

    /** @param Campaign $object */
    protected function getZoneCodes(object $object): ?array
    {
        if (!$object->isNationalVisibility() && ($zone = $object->getZones()->first())) {
            return $this->buildZoneCodes($zone);
        }

        return null;
    }
}
