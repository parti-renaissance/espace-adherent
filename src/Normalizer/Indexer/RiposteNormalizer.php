<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Jecoute\Riposte;

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

    /** @param Riposte $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getBody();
    }

    /** @param Riposte $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->getCreatedAt();
    }

    /** @param Riposte $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->getAuthor();
    }

    /** @param Riposte $object */
    protected function isNational(object $object): bool
    {
        return true;
    }

    /** @param Riposte $object */
    protected function getUrl(object $object): ?string
    {
        return $object->getSourceUrl();
    }

    /** @param Riposte $object */
    protected function getMediaType(object $object): ?string
    {
        return $object->getOpenGraph() && isset($object->getOpenGraph()['site_name']) ? $object->getOpenGraph()['site_name'] : null;
    }
}
