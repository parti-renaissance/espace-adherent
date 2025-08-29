<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\TimelineItemPrivateMessage;

class PrivateMessageNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return TimelineItemPrivateMessage::class;
    }

    /** @param TimelineItemPrivateMessage $object */
    protected function getTitle(object $object): string
    {
        return $object->title;
    }

    /** @param TimelineItemPrivateMessage $object */
    protected function getDescription(object $object): ?string
    {
        return $object->description;
    }

    /** @param TimelineItemPrivateMessage $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->getCreatedAt();
    }

    /** @param TimelineItemPrivateMessage $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return null;
    }

    /** @param TimelineItemPrivateMessage $object */
    protected function getAdherentIds(object $object): ?array
    {
        return array_map(fn (Adherent $adherent) => $adherent->getId(), $object->adherents->toArray());
    }

    /** @param TimelineItemPrivateMessage $object */
    protected function getCtaLabel(object $object): ?string
    {
        return $object->ctaLabel;
    }

    /** @param TimelineItemPrivateMessage $object */
    protected function getCtaLink(object $object): ?string
    {
        return $object->ctaUrl;
    }
}
