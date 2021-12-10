<?php

namespace App\Normalizer\Indexer;

use App\Entity\Jecoute\Riposte;
use App\JeMengageTimelineFeed\JeMengageTimelineFeedEnum;

class RiposteNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return Riposte::class;
    }

    /** @param Riposte $object */
    protected function getTitle(object $object): string
    {
        return $object->getTitle();
    }

    protected function getType(): string
    {
        return JeMengageTimelineFeedEnum::RIPOSTE;
    }

    /** @param Riposte $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getBody();
    }

    /** @param Riposte $object */
    protected function isLocal(object $object): bool
    {
        return false;
    }

    /** @param Riposte $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param Riposte $object */
    protected function getTimeZone(object $object): ?string
    {
        return null;
    }

    /** @param Riposte $object */
    protected function getAuthor(object $object): ?string
    {
        return $object->getAuthor() ? $object->getAuthor()->getFullName() : null;
    }
}
