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
    public function getAudience(mixed $object): array
    {
        $enabledFilters = parent::getAudience($object);
        $filter = $object->getFilter();

        if (!$filter instanceof AudienceFilter) {
            return $enabledFilters;
        }

        $audienceKeys = $audienceExcludeKeys = [];

        // Tags
        foreach (array_filter([$filter->adherentTags, $filter->electTags, $filter->staticTags]) as $tag) {
            if (str_starts_with($tag, '!')) {
                $audienceExcludeKeys[] = 'tag:'.substr($tag, 1);
            } else {
                $enabledFilters['tag'] = true;
                $audienceKeys[] = 'tag:'.$tag;
            }
        }

        // Zones
        $zones = [];
        if ($filter->getZone()) {
            $zones = [$filter->getZone()];
        } elseif (!$filter->getZones()->isEmpty()) {
            $zones = $filter->getZones()->toArray();
        }

        foreach ($zones as $zone) {
            foreach ($this->buildZoneCodes($zone) as $code) {
                $audienceKeys[] = 'zone:'.$code;
            }
            $enabledFilters['zone'] = true;
        }

        // Committee
        if ($filter->getCommittee()) {
            $audienceKeys[] = 'committee:'.$filter->getCommittee()->getUuid()->toString();
            $enabledFilters['committee'] = true;
        }

        // Mandate type
        if ($type = $filter->getMandateType()) {
            if (str_starts_with($type, '!')) {
                $audienceExcludeKeys[] = 'mandate_type:'.substr($type, 1);
            } else {
                $audienceKeys[] = 'mandate_type:'.$type;
                $enabledFilters['mandate_type'] = true;
            }
        }

        // Declared mandate
        if ($declared = $filter->getDeclaredMandate()) {
            if (str_starts_with($declared, '!')) {
                $audienceExcludeKeys[] = 'declared_mandate:'.substr($declared, 1);
            } else {
                $audienceKeys[] = 'declared_mandate:'.$declared;
                $enabledFilters['declared_mandate'] = true;
            }
        }

        // Dates
        $audience = [];

        if ($filter->firstMembershipSince) {
            $audience['first_membership_since'] = $filter->firstMembershipSince->getTimestamp();
            $enabledFilters['first_membership_since'] = true;
        }

        if ($filter->firstMembershipBefore) {
            $audience['first_membership_before'] = $filter->firstMembershipBefore->getTimestamp();
            $enabledFilters['first_membership_before'] = true;
        }

        if ($filter->getLastMembershipSince()) {
            $audience['last_membership_since'] = $filter->getLastMembershipSince()->getTimestamp();
            $enabledFilters['last_membership_since'] = true;
        }

        if ($filter->getLastMembershipBefore()) {
            $audience['last_membership_before'] = $filter->getLastMembershipBefore()->getTimestamp();
            $enabledFilters['last_membership_before'] = true;
        }

        if ($filter->getRegisteredSince()) {
            $audience['registered_since'] = $filter->getRegisteredSince()->getTimestamp();
            $enabledFilters['registered_since'] = true;
        }

        if ($filter->getRegisteredUntil()) {
            $audience['registered_before'] = $filter->getRegisteredUntil()->getTimestamp();
            $enabledFilters['registered_before'] = true;
        }

        if ($audienceKeys) {
            $audience['include'] = array_values(array_unique($audienceKeys));
        }

        if ($audienceExcludeKeys) {
            $audience['exclude'] = array_values(array_unique($audienceExcludeKeys));
        }

        return array_merge($enabledFilters, $audience);
    }
}
