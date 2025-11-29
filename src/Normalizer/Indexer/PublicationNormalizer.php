<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use App\AdherentMessage\PublicationZone;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\Geo\Zone;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    protected function getAuthorRole(object $object): ?string
    {
        return $object->senderRole;
    }

    /** @param AdherentMessage $object */
    protected function getAuthorInstance(object $object): ?string
    {
        return $object->senderInstance;
    }

    /** @param AdherentMessage $object */
    protected function getAuthorZone(object $object): ?string
    {
        return $object->senderZone;
    }

    /** @param AdherentMessage $object */
    protected function getAuthorTheme(object $object): ?array
    {
        return $object->senderTheme;
    }

    /** @param AdherentMessage $object */
    protected function getAuthorImageUrl(object $object): ?string
    {
        $sender = $object->getSender();

        return $sender?->getImageName() ? $this->urlGenerator->generate(
            'asset_url',
            ['path' => $sender->getImagePath()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;
    }

    /** @param AdherentMessage $object */
    protected function getAccessAuthorId(object $object): ?int
    {
        return $object->getAuthor()?->getId();
    }

    /** @param AdherentMessage $object */
    protected function getAccessTeamOwnerId(object $object): ?int
    {
        return $object->teamOwner?->getId();
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
                $audienceKeys[] = 'tag:'.$tag;
            }
            $enabledFilters['tag'] = true;
        }

        /* @var Zone[] $zones */
        $zones = $filter->getZones()->toArray();
        if ($filter->getZone()) {
            $zones[] = $filter->getZone();
        }

        $zoneTypeFilter = array_fill_keys(PublicationZone::ZONE_TYPES, false);
        foreach ($zones as $zone) {
            $zoneTypeFilter[$zone->getType()] = $zone->getCode();
        }

        foreach ($zoneTypeFilter as $type => $code) {
            $audienceKeys[] = \sprintf('zone:%s:%s', $type, false === $code ? 'none' : $code);
            $enabledFilters['zone'] = $enabledFilters['zone'] || false !== $code;
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
            }
            $enabledFilters['mandate_type'] = true;
        }

        // Declared mandate
        if ($declared = $filter->getDeclaredMandate()) {
            if (str_starts_with($declared, '!')) {
                $audienceExcludeKeys[] = 'declared_mandate:'.substr($declared, 1);
            } else {
                $audienceKeys[] = 'declared_mandate:'.$declared;
            }
            $enabledFilters['declared_mandate'] = true;
        }

        if ($filter->getGender()) {
            $audienceKeys[] = 'gender:'.$filter->getGender();
            $enabledFilters['civility'] = true;
        }

        if ($filter->getAgeMin()) {
            $enabledFilters['age_min'] = $filter->getAgeMin();
        }

        if ($filter->getAgeMax()) {
            $enabledFilters['age_max'] = $filter->getAgeMax();
        }

        if (null !== $filter->getIsCommitteeMember()) {
            $audienceKeys[] = 'is_committee_member:'.($filter->getIsCommitteeMember() ? '1' : '0');
            $enabledFilters['committee_member'] = true;
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
