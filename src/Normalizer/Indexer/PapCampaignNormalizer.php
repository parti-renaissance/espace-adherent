<?php

namespace App\Normalizer\Indexer;

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
}
