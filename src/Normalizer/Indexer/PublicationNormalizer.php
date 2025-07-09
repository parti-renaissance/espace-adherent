<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;

class PublicationNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return AdherentMessage::class;
    }

    /** @param AdherentMessage $object */
    protected function getTitle(object $object): string
    {
        return $object->getSubject();
    }

    /** @param AdherentMessage $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getJsonContent();
    }

    /** @param AdherentMessage $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->getSentAt();
    }

    /** @param AdherentMessage $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->getSender();
    }

    /** @param AdherentMessage $object */
    public function getAudience(mixed $object): ?array
    {
        $filter = $object->getFilter();
        if (!$filter instanceof AudienceFilter) {
            return null;
        }

        $audience = [];

        $showTags = $hideTags = [];

        foreach (array_filter([$filter->adherentTags, $filter->electTags, $filter->staticTags]) as $tag) {
            if (str_starts_with($tag, '!')) {
                $hideTags[] = substr($tag, 1);
            } else {
                $showTags[] = $tag;
            }
        }

        if ($showTags) {
            $audience['show_tags'] = $showTags;
        }

        if ($hideTags) {
            $audience['hide_tags'] = $hideTags;
        }

        if ($zones = $this->getZoneCodes($object)) {
            $audience['zones'] = $zones;
        }

        return $audience;
    }

    /** @param AdherentMessage $object */
    protected function getCommitteeUuid(object $object): ?string
    {
        $filter = $object->getFilter();
        if (!$filter instanceof AudienceFilter) {
            return null;
        }

        return $filter->getCommittee()?->getUuid()?->toString();
    }

    /** @param AdherentMessage $object */
    protected function getZoneCodes(object $object): ?array
    {
        $filter = $object->getFilter();
        if (!$filter instanceof AudienceFilter) {
            return null;
        }

        if ($filter->getZone()) {
            $zones = [$filter->getZone()];
        } elseif (!$filter->getZones()->isEmpty()) {
            $zones = $filter->getZones()->toArray();
        }

        return empty($zones) ? null : array_map([$this, 'buildZoneCodes'], $zones);
    }
}
