<?php

namespace App\Normalizer\Indexer;

use App\Entity\Pap\Campaign;
use App\JeMengageTimelineFeed\JeMengageTimelineFeedEnum;

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

    protected function getType(): string
    {
        return JeMengageTimelineFeedEnum::PAP_CAMPAIGN;
    }

    /** @param Campaign $object */
    protected function getDescription(object $object): ?string
    {
        return 'Nouvelle campagne de porte-à-porte autour de vous.';
    }

    /** @param Campaign $object */
    protected function isLocal(object $object): bool
    {
        return false;
    }

    /** @param Campaign $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param Campaign $object */
    protected function getTimeZone(object $object): ?string
    {
        return null;
    }

    /** @param Campaign $object */
    protected function getAuthor(object $object): ?string
    {
        return null;
    }
}
