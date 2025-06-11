<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;

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

    /** @param News $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getContent();
    }

    /** @param News $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param News $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->getAuthor();
    }

    /** @param News $object */
    protected function isNational(object $object): bool
    {
        return $object->isNationalVisibility();
    }

    /** @param News $object */
    protected function getZoneCodes(object $object): ?array
    {
        return $this->buildZoneCodes($object->committee ? $object->committee->getAssemblyZone() : $object->getZone());
    }

    /** @param News $object */
    protected function getCtaLabel(object $object): ?string
    {
        return $object->getLinkLabel();
    }

    /** @param News $object */
    protected function getCtaLink(object $object): ?string
    {
        return $object->getExternalLink();
    }
}
