<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Timeline\CandidateSelection;

use App\JeMengage\Timeline\CandidateSelection\AudienceContext;
use App\JeMengage\Timeline\CandidateSelection\AudienceMatcher;
use App\JeMengage\Timeline\Indexer\UserProfile;
use App\JeMengage\Timeline\Mirror\TimelineFeedTransformer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Schema drift lock between TimelineFeedTransformer (what the mirror stores in `audience`) and
 * AudienceMatcher (what the read path knows how to evaluate). If the transformer starts producing a
 * key the matcher does not whitelist, every row carrying it would be fail-closed rejected — this
 * test turns that silent product regression into a build failure.
 */
final class TransformerMatcherSchemaTest extends TestCase
{
    public function testPublicationDocumentProducesExactlyTheMatcherKnownKeys(): void
    {
        $audience = new TimelineFeedTransformer()->transform(self::publicationDocument())['audience'];

        // Both directions: nothing unknown to the matcher, and the representative document does
        // exercise every scalar facet (an empty diff on a shrunken output would prove nothing).
        self::assertEqualsCanonicalizing(
            array_keys(AudienceMatcher::INCLUDE_KEYS),
            array_merge(array_keys($audience['include']), ['national', 'adherent_ids', 'agoras']),
            'Publication audience include keys must cover the matcher whitelist (reach-only keys aside).'
        );
        self::assertEqualsCanonicalizing(array_keys(AudienceMatcher::EXCLUDE_KEYS), array_keys($audience['exclude']));
    }

    public function testEventDocumentProducesOnlyMatcherKnownReachKeys(): void
    {
        $audience = new TimelineFeedTransformer()->transform(self::eventDocument())['audience'];

        self::assertEqualsCanonicalizing(
            ['national', 'zones', 'adherent_ids', 'committees', 'agoras'],
            array_keys($audience['include'])
        );
        self::assertArrayNotHasKey('exclude', $audience);
    }

    public function testTransformerOutputPassesTheMatcherSchemaGuard(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');
        $matcher = new AudienceMatcher($logger);
        $transformer = new TimelineFeedTransformer();

        foreach (['publication' => self::publicationDocument(), 'event' => self::eventDocument()] as $type => $document) {
            // The verdict is irrelevant here: a schema-guard rejection would log a warning.
            $matcher->matches(self::emptyContext(), $type, $transformer->transform($document)['audience']);
        }
    }

    /**
     * Shaped like the PublicationNormalizer output: enabled-filter flags + scalar facets + date
     * timestamps at the top level, stringly-typed include/exclude key lists.
     */
    private static function publicationDocument(): array
    {
        return [
            'type' => 'publication',
            'date' => '2026-06-01T10:00:00+02:00',
            'audience' => [
                'tag' => true,
                'zone' => true,
                'committee' => true,
                'mandate_type' => true,
                'declared_mandate' => true,
                'civility' => true,
                'age_min' => 18,
                'age_max' => 65,
                'committee_member' => 1,
                'scope_targets' => true,
                'first_membership_since' => 1700000000,
                'first_membership_before' => 1700000001,
                'last_membership_since' => 1700000002,
                'last_membership_before' => 1700000003,
                'registered_since' => 1700000004,
                'registered_before' => 1700000005,
                'include' => [
                    'tag:adherent',
                    'zone:department:75',
                    'zone:city:none',
                    'committee:cmt-1111',
                    'mandate_type:depute',
                    'declared_mandate:maire',
                    'gender:female',
                    'scope_targets:referent',
                    'scope_targets:referent:*',
                ],
                'exclude' => [
                    'tag:exclu',
                    'mandate_type:senateur',
                    'declared_mandate:conseiller',
                ],
            ],
        ];
    }

    /**
     * Shaped like a reach-indexed event record (AbstractJeMengageTimelineFeedNormalizer): root reach
     * fields + the default audience flags (all disabled, committee_member sentinel 2).
     */
    private static function eventDocument(): array
    {
        return [
            'type' => 'event',
            'date' => '2026-06-01T10:00:00+02:00',
            'begin_at' => '2026-06-10T18:00:00+02:00',
            'is_national' => true,
            'zone_codes' => ['department_75', 'city_75056'],
            'adherent_ids' => [42],
            'committee_uuid' => 'cmt-1111',
            'agora_uuid' => 'agora-1111',
            'audience' => [
                'tag' => false,
                'zone' => false,
                'committee' => false,
                'mandate_type' => false,
                'declared_mandate' => false,
                'civility' => false,
                'age_min' => false,
                'age_max' => false,
                'committee_member' => 2,
                'scope_targets' => false,
                'first_membership_since' => false,
                'first_membership_before' => false,
                'last_membership_since' => false,
                'last_membership_before' => false,
                'registered_since' => false,
                'registered_before' => false,
            ],
        ];
    }

    private static function emptyContext(): AudienceContext
    {
        return new AudienceContext(
            new UserProfile(1, [], [], [], [], [], [], 0, []),
            [],
            [],
            [],
        );
    }
}
