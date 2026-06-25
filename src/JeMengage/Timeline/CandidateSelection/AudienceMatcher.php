<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\CandidateSelection;

use App\AdherentMessage\PublicationZone;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Psr\Log\LoggerInterface;

/**
 * Local visibility matcher — normative spec: DESIGN.md § Spec de matching (strict parity with the
 * Algolia clauses built by GetTimelineFeedsController). Security boundary rules:
 * - absent key = no constraint (verified equivalence with the Algolia boolean flags);
 * - any unknown or malformed audience key = fail-closed reject (schema drift guard).
 */
class AudienceMatcher
{
    /**
     * Whitelists of the audience keys this matcher knows how to evaluate, with their expected value
     * type. Public: the transformer/matcher schema cross-check test reads them. 'committees'/'agoras'
     * reach is known but intentionally NOT a base clause grant (Algolia parity) — it is only matched
     * by the view-filter conditions (plan phase 5).
     */
    public const array INCLUDE_KEYS = [
        'national' => 'bool',
        'zones' => 'list',
        'adherent_ids' => 'list',
        'committees' => 'list',
        'agoras' => 'list',
        'tags' => 'list',
        'civility' => 'string',
        'age_min' => 'int',
        'age_max' => 'int',
        'committee_member' => 'int',
        'mandate_types' => 'list',
        'declared_mandates' => 'list',
        'scope_targets' => 'list',
        'first_membership_since' => 'string',
        'first_membership_before' => 'string',
        'last_membership_since' => 'string',
        'last_membership_before' => 'string',
        'registered_since' => 'string',
        'registered_before' => 'string',
    ];
    public const array EXCLUDE_KEYS = [
        'tags' => 'list',
        'mandate_types' => 'list',
        'declared_mandates' => 'list',
    ];

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function matches(AudienceContext $context, string $type, ?array $audience, ?TimelineRequestFilter $filter = null): bool
    {
        $audience ??= [];
        $include = $audience['include'] ?? [];
        $exclude = $audience['exclude'] ?? [];

        if (!\is_array($include) || !\is_array($exclude) || !$this->hasKnownSchema($audience, $include, $exclude)) {
            return false;
        }

        return $this->matchesBaseClause($context, $type, $include)            // rule 1
            && $this->matchesIncludeTags($context, $type, $include)           // rule 2
            && $this->matchesExcludeTags($context, $exclude)                  // rule 3
            && $this->matchesPublicationZones($context, $type, $include)      // rule 4
            && $this->matchesCommittee($context, $type, $include)             // rule 5
            && $this->matchesAgeBounds($context, $include)                    // rules 6-7
            && $this->matchesCivility($context, $type, $include)              // rule 8
            && $this->matchesCommitteeMember($context, $type, $include)       // rule 9
            && $this->matchesMandates($context, $type, $include, $exclude)    // rule 10
            && $this->matchesDeclaredMandates($context, $type, $include, $exclude) // rule 11
            && $this->matchesDateFacets($context, $include)                   // rule 12
            && $this->matchesScopeTargets($context, $type, $include)          // rule 13
            && $this->matchesViewFilter($type, $include, $filter);            // rules 14-15
    }

    /**
     * Fail-closed schema guard: a row carrying keys this matcher does not evaluate — or known keys
     * with a malformed value type — is never served by default. Type validation here is what lets
     * the rules below consume the buckets without crashing on a corrupt row (a TypeError in the
     * read path would take the whole feed down for every user).
     */
    private function hasKnownSchema(array $audience, array $include, array $exclude): bool
    {
        $unknown = array_filter([
            'audience' => array_diff(array_keys($audience), ['include', 'exclude']),
            'include' => $this->invalidBucketKeys($include, self::INCLUDE_KEYS),
            'exclude' => $this->invalidBucketKeys($exclude, self::EXCLUDE_KEYS),
        ]);

        if ([] === $unknown) {
            return true;
        }

        $this->logger->warning('Timeline audience row with unknown keys rejected (fail-closed).', [
            'unknown' => $unknown,
        ]);

        return false;
    }

    /**
     * @param array<string, string> $whitelist key => expected type ('list'|'bool')
     *
     * @return string[] keys that are unknown or carry a malformed value
     */
    private function invalidBucketKeys(array $bucket, array $whitelist): array
    {
        $invalid = [];

        foreach ($bucket as $key => $value) {
            $valid = match ($whitelist[$key] ?? null) {
                'list' => \is_array($value),
                'bool' => \is_bool($value),
                'int' => \is_int($value),
                'string' => \is_string($value),
                default => false,
            };

            if (!$valid) {
                $invalid[] = $key;
            }
        }

        return $invalid;
    }

    /**
     * Rule 1 — Algolia base clause: publications always pass (their own facets gate them below);
     * anything else needs a reach grant: national broadcast, direct adherent id, or a reach zone
     * among the user assembly/city zones. Committee/agora reach is intentionally NOT a grant
     * (Algolia parity: the base clause never matches committee_uuid/agora_uuid).
     */
    private function matchesBaseClause(AudienceContext $context, string $type, array $include): bool
    {
        if (TimelineFeedTypeEnum::PUBLICATION === $type) {
            return true;
        }

        if (true === ($include['national'] ?? false)) {
            return true;
        }

        if (\in_array($context->profile->userId, $include['adherent_ids'] ?? [], true)) {
            return true;
        }

        return [] !== array_intersect($include['zones'] ?? [], $context->reachZones);
    }

    /**
     * Rule 2 — publication tag targeting: any stored tag must match one of the user tag prefixes.
     */
    private function matchesIncludeTags(AudienceContext $context, string $type, array $include): bool
    {
        if (TimelineFeedTypeEnum::PUBLICATION !== $type || empty($include['tags'])) {
            return true;
        }

        return [] !== array_intersect($include['tags'], $context->tagPrefixes);
    }

    /**
     * Rule 3 — tag exclusion, applied to ALL types (the Algolia NOT conditions are not type-scoped).
     */
    private function matchesExcludeTags(AudienceContext $context, array $exclude): bool
    {
        return [] === array_intersect($exclude['tags'] ?? [], $context->tagPrefixes);
    }

    /**
     * Rule 4 — publication zone targeting: AND across the 9 publication zone types. For each type,
     * the publication either does not constrain it (no entry), opts out ("type:none" sentinel) or
     * must match one of the user deep zone codes of that type.
     */
    private function matchesPublicationZones(AudienceContext $context, string $type, array $include): bool
    {
        if (TimelineFeedTypeEnum::PUBLICATION !== $type || empty($include['zones'])) {
            return true;
        }

        foreach (PublicationZone::ZONE_TYPES as $zoneType) {
            if (!$this->matchesZoneType($context, $zoneType, $include['zones'])) {
                return false;
            }
        }

        return true;
    }

    private function matchesZoneType(AudienceContext $context, string $zoneType, array $zones): bool
    {
        $prefix = $zoneType.':';
        $targeted = array_filter($zones, static function ($zone) use ($prefix): bool {
            return \is_string($zone) && str_starts_with($zone, $prefix);
        });

        if ([] === $targeted) {
            return true;
        }

        if (\in_array($zoneType.':none', $targeted, true)) {
            return true;
        }

        foreach ($context->zoneCodesByType[$zoneType] ?? [] as $code) {
            if (\in_array($zoneType.':'.$code, $targeted, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rule 5 — publication committee targeting.
     */
    private function matchesCommittee(AudienceContext $context, string $type, array $include): bool
    {
        if (TimelineFeedTypeEnum::PUBLICATION !== $type || empty($include['committees'])) {
            return true;
        }

        return [] !== array_intersect($include['committees'], $context->profile->committees);
    }

    /**
     * Rules 6-7 — age bounds. The Algolia numeric clauses compare against (age ?? 0), so a user
     * without an age fails any age_min (fail-closed) but passes any age_max — that fail-open quirk
     * is replicated on purpose (strict parity with the fallback path).
     */
    private function matchesAgeBounds(AudienceContext $context, array $include): bool
    {
        $age = $context->profile->age ?? 0;

        if (isset($include['age_min']) && $age < $include['age_min']) {
            return false;
        }

        if (isset($include['age_max']) && $age > $include['age_max']) {
            return false;
        }

        return true;
    }

    /**
     * Rule 8 — publication civility targeting (a user without a gender never matches, fail-closed).
     */
    private function matchesCivility(AudienceContext $context, string $type, array $include): bool
    {
        if (TimelineFeedTypeEnum::PUBLICATION !== $type || !isset($include['civility'])) {
            return true;
        }

        return $include['civility'] === $context->profile->civility;
    }

    /**
     * Rule 9 — publication committee-membership flag (0|1; the "no constraint" sentinel 2 is never
     * stored by the transformer).
     */
    private function matchesCommitteeMember(AudienceContext $context, string $type, array $include): bool
    {
        if (TimelineFeedTypeEnum::PUBLICATION !== $type || !isset($include['committee_member'])) {
            return true;
        }

        return $include['committee_member'] === $context->profile->committeeMember;
    }

    /**
     * Rule 10 — elected mandate targeting: the include constraint is publication-scoped, the
     * exclusion applies to ALL types (the Algolia NOT conditions are not type-scoped).
     */
    private function matchesMandates(AudienceContext $context, string $type, array $include, array $exclude): bool
    {
        if ([] !== array_intersect($exclude['mandate_types'] ?? [], $context->profile->mandateTypes)) {
            return false;
        }

        if (TimelineFeedTypeEnum::PUBLICATION !== $type || empty($include['mandate_types'])) {
            return true;
        }

        return [] !== array_intersect($include['mandate_types'], $context->profile->mandateTypes);
    }

    /**
     * Rule 11 — declared mandate targeting, same shape as rule 10.
     */
    private function matchesDeclaredMandates(AudienceContext $context, string $type, array $include, array $exclude): bool
    {
        if ([] !== array_intersect($exclude['declared_mandates'] ?? [], $context->profile->declaredMandates)) {
            return false;
        }

        if (TimelineFeedTypeEnum::PUBLICATION !== $type || empty($include['declared_mandates'])) {
            return true;
        }

        return [] !== array_intersect($include['declared_mandates'], $context->profile->declaredMandates);
    }

    /**
     * Rule 12 — membership/registration date bounds. A date constraint on a user without that date
     * never matches (fail-closed, Algolia parity). Both sides share the "Y-m-d\TH:i:s\Z" UTC format,
     * so lexicographic comparison equals chronological comparison.
     */
    private function matchesDateFacets(AudienceContext $context, array $include): bool
    {
        $facets = [
            'first_membership_since' => [$context->profile->firstMembershipDate, 'since'],
            'first_membership_before' => [$context->profile->firstMembershipDate, 'before'],
            'last_membership_since' => [$context->profile->lastMembershipDate, 'since'],
            'last_membership_before' => [$context->profile->lastMembershipDate, 'before'],
            'registered_since' => [$context->profile->registeredDate, 'since'],
            'registered_before' => [$context->profile->registeredDate, 'before'],
        ];

        foreach ($facets as $facet => [$userDate, $bound]) {
            if (!isset($include[$facet])) {
                continue;
            }

            if (null === $userDate) {
                return false;
            }

            if ('since' === $bound ? $include[$facet] > $userDate : $include[$facet] < $userDate) {
                return false;
            }
        }

        return true;
    }

    /**
     * Rule 13 — publication scope-target keys, exact string matching against the resolver output
     * (e.g. "referent", "referent:*", "referent:role").
     */
    private function matchesScopeTargets(AudienceContext $context, string $type, array $include): bool
    {
        if (TimelineFeedTypeEnum::PUBLICATION !== $type || empty($include['scope_targets'])) {
            return true;
        }

        return [] !== array_intersect($include['scope_targets'], $context->profile->scopeTargets);
    }

    /**
     * Rules 14-15 — resolved view-filter conditions (query params), ANDed. Zone/committee/agora are
     * REACH filters: publications are excluded by construction (Algolia parity — their include
     * buckets carry TARGETING, not reach; without the type guard a publication targeting
     * "department:75" would wrongly surface in the department 75 filtered view, while Algolia
     * excludes it because the publication record has no zone_codes/committee_uuid reach field).
     */
    private function matchesViewFilter(string $type, array $include, ?TimelineRequestFilter $filter): bool
    {
        if (null === $filter) {
            return true;
        }

        foreach ($filter->conditions as $condition) {
            $matched = match ($condition->kind) {
                RequestFilterCondition::NATIONAL => true === ($include['national'] ?? false),
                RequestFilterCondition::ZONE => TimelineFeedTypeEnum::PUBLICATION !== $type
                    && \in_array($condition->value, $include['zones'] ?? [], true),
                RequestFilterCondition::COMMITTEE => TimelineFeedTypeEnum::PUBLICATION !== $type
                    && \in_array($condition->value, $include['committees'] ?? [], true),
                RequestFilterCondition::AGORA => TimelineFeedTypeEnum::PUBLICATION !== $type
                    && \in_array($condition->value, $include['agoras'] ?? [], true),
                default => false, // unknown condition kind: fail-closed
            };

            if (!$matched) {
                return false;
            }
        }

        return true;
    }
}
