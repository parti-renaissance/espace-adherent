<?php

namespace App\Normalizer\Indexer;

use App\Entity\Jecoute\News;
use App\JeMengageTimelineFeed\JeMengageTimelineFeedEnum;

class NewsNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return News::class;
    }

    /** @param News $object */
    protected function getTitle(object $object): string
    {
        return $object->getTitle();
    }

    protected function getType(): string
    {
        return JeMengageTimelineFeedEnum::NEWS;
    }

    /** @param News $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getText();
    }

    /** @param News $object */
    protected function isLocal(object $object): bool
    {
        return null !== $object->getSpace();
    }

    /** @param News $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param News $object */
    protected function getAuthor(object $object): ?string
    {
        return $object->getAuthor() ? $object->getAuthor()->getFullName() : null;
    }
}
