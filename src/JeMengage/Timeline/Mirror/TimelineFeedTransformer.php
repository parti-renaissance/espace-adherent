<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror;

use App\Entity\Geo\Zone;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;

/**
 * Derives the canonical timeline_feed model from the flat record produced by the timeline
 * normalizers (AbstractJeMengageTimelineFeedNormalizer family).
 *
 * Pure (no I/O): it only reshapes the normalizer output, which stays the single source of targeting
 * truth. It re-buckets the reach fields (events/news/actions/social posts) and the publication
 * audience facets into a typed {include, exclude} model, extracts the publication/event dates, and
 * projects the record onto the app display contract.
 */
class TimelineFeedTransformer
{
    private const DISPLAY_KEYS = [
        'access',
        'address',
        'agora_uuid',
        'author',
        'begin_at',
        'category',
        'committee_uuid',
        'cta_label',
        'cta_link',
        'date',
        'description',
        'finish_at',
        'identifier',
        'image',
        'is_national',
        'live_url',
        'media',
        'media_type',
        'mode',
        'objectID',
        'participants_count',
        'post_address',
        'time_zone',
        'title',
        'type',
        'url',
        'visibility',
        'zone_codes',
    ];

    /**
     * Membership/registration date facets, carried as Unix timestamps in the publication audience.
     */
    private const DATE_FACETS = [
        'first_membership_since',
        'first_membership_before',
        'last_membership_since',
        'last_membership_before',
        'registered_since',
        'registered_before',
    ];

    /**
     * @param array<string, mixed> $document the flat normalizer record (+ objectID)
     *
     * @return array{type: string, publicationDate: \DateTimeImmutable, eventDate: ?\DateTimeImmutable, audience: ?array, display: array<string, mixed>, visibility: ?string, committeeUuid: ?string, agoraUuid: ?string}
     */
    public function transform(array $document): array
    {
        return [
            'type' => $document['type'],
            // publication_date is required downstream but getDate() is nullable: fall back to now.
            'publicationDate' => $this->parseDate($document['date'] ?? null) ?? new \DateTimeImmutable(),
            'eventDate' => $this->parseDate($this->eventDate($document)),
            'audience' => $this->buildAudience($document),
            'display' => array_intersect_key($document, array_flip(self::DISPLAY_KEYS)),
            'visibility' => $document['visibility'] ?? null,
            'committeeUuid' => $document['committee_uuid'] ?? null,
            'agoraUuid' => $document['agora_uuid'] ?? null,
        ];
    }

    private function eventDate(array $document): ?string
    {
        if (TimelineFeedTypeEnum::POLL === ($document['type'] ?? null)) {
            return $document['finish_at'] ?? null;
        }

        return $document['begin_at'] ?? null;
    }

    private function buildAudience(array $document): ?array
    {
        $facets = \is_array($document['audience'] ?? null) ? $document['audience'] : [];

        $include = array_merge(
            $this->reachInclude($document),
            $this->parseKeys($facets['include'] ?? []),
            $this->scalarFacets($facets),
        );
        $exclude = $this->parseKeys($facets['exclude'] ?? []);

        $audience = [];
        if ($include) {
            $audience['include'] = $include;
        }
        if ($exclude) {
            $audience['exclude'] = $exclude;
        }

        return $audience ?: null;
    }

    /**
     * Reach targeting (non-publication items): national flag, zones, registrants, committee, agora.
     *
     * @return array<string, mixed>
     */
    private function reachInclude(array $document): array
    {
        $include = [];

        if ($document['is_national'] ?? false) {
            $include['national'] = true;
        }
        if ($zones = $document['zone_codes'] ?? null) {
            $include['zones'] = array_values(array_map([$this, 'normalizeReachZone'], $zones));
        }
        if ($adherentIds = $document['adherent_ids'] ?? null) {
            $include['adherent_ids'] = array_values($adherentIds);
        }
        if ($committeeUuid = $document['committee_uuid'] ?? null) {
            $include['committees'] = [$committeeUuid];
        }
        if ($agoraUuid = $document['agora_uuid'] ?? null) {
            $include['agoras'] = [$agoraUuid];
        }

        return $include;
    }

    /**
     * Reach zones come from Zone::getTypeCode() as "type_code" (e.g. "department_75"); the canonical
     * model uses "type:code" (e.g. "department:75"), the same shape publication zones already use.
     * A zone type may itself contain underscores ("city_community", "foreign_district"), so the
     * split matches the known zone types and keeps the longest matching prefix — not the first "_".
     */
    private function normalizeReachZone(string $code): string
    {
        $matchedType = null;
        foreach (Zone::TYPES as $type) {
            if (str_starts_with($code, $type.'_') && (null === $matchedType || \strlen($type) > \strlen($matchedType))) {
                $matchedType = $type;
            }
        }

        return null === $matchedType ? $code : $matchedType.':'.substr($code, \strlen($matchedType) + 1);
    }

    /**
     * Parses the publication stringly-typed keys ("prefix:value") of an include/exclude bucket into
     * typed dimensions. Splits on the first colon only, so multi-segment values (zones, scope
     * targets like "role:*" or "role:team_role") are preserved verbatim.
     *
     * @param string[] $keys
     *
     * @return array<string, mixed>
     */
    private function parseKeys(array $keys): array
    {
        $bucket = [];

        foreach ($keys as $key) {
            [$prefix, $value] = explode(':', $key, 2) + [1 => ''];

            switch ($prefix) {
                case 'tag':
                    $bucket['tags'][] = $value;
                    break;
                case 'zone':
                    $bucket['zones'][] = $value;
                    break;
                case 'committee':
                    $bucket['committees'][] = $value;
                    break;
                case 'mandate_type':
                    $bucket['mandate_types'][] = $value;
                    break;
                case 'declared_mandate':
                    $bucket['declared_mandates'][] = $value;
                    break;
                case 'gender':
                    $bucket['civility'] = $value;
                    break;
                case 'scope_targets':
                    $bucket['scope_targets'][] = $value;
                    break;
            }
        }

        return $bucket;
    }

    /**
     * Publication scalar facets carried alongside the include/exclude key lists: age bounds,
     * committee membership (sentinel 2 = no constraint), and date bounds (Unix timestamps).
     *
     * @return array<string, mixed>
     */
    private function scalarFacets(array $facets): array
    {
        $include = [];

        if (\is_int($facets['age_min'] ?? false)) {
            $include['age_min'] = $facets['age_min'];
        }
        if (\is_int($facets['age_max'] ?? false)) {
            $include['age_max'] = $facets['age_max'];
        }

        $member = $facets['committee_member'] ?? 2;
        if (1 === $member || 0 === $member) {
            $include['committee_member'] = $member;
        }

        foreach (self::DATE_FACETS as $facet) {
            if (\is_int($facets[$facet] ?? false)) {
                $include[$facet] = new \DateTimeImmutable('@'.$facets[$facet])->format('Y-m-d\TH:i:s\Z');
            }
        }

        return $include;
    }

    private function parseDate(?string $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }
}
