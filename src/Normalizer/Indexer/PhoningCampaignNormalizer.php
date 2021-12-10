<?php

namespace App\Normalizer\Indexer;

use App\Entity\Phoning\Campaign;
use App\JeMengageTimelineFeed\JeMengageTimelineFeedEnum;

class PhoningCampaignNormalizer extends AbstractJeMengageTimelineFeedNormalizer
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
        return JeMengageTimelineFeedEnum::PHONING_CAMPAIGN;
    }

    /** @param Campaign $object */
    protected function getDescription(object $object): ?string
    {
        return sprintf('Vous avez jusqu\'au %s pour remplir %s questionnaires.',
            $this->formatDate($object->getFinishAt()),
            $object->getGoal()
        );
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
    protected function getAuthor(object $object): ?string
    {
        return $object->getCreatedByAdherent() ? $object->getCreatedByAdherent()->getFullName() : null;
    }
}
