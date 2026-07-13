<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror;

use App\JeMengage\Timeline\Mirror\TimelineFeedTransformer;
use PHPUnit\Framework\TestCase;

class TimelineFeedTransformerTest extends TestCase
{
    private TimelineFeedTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new TimelineFeedTransformer();
    }

    public function testEventReachIsBucketedIntoInclude(): void
    {
        $result = $this->transformer->transform([
            'type' => 'event',
            'objectID' => 'evt-1',
            'title' => 'My event',
            'date' => '2026-05-20T10:00:00+00:00',
            'begin_at' => '2026-06-01T18:00:00+02:00',
            'is_national' => false,
            'zone_codes' => ['department_75', 'region_11'],
            'adherent_ids' => [1, 2, 3],
            'committee_uuid' => 'committee-uuid',
            'agora_uuid' => null,
            'audience' => $this->defaultFacets(),
        ]);

        self::assertSame('event', $result['type']);
        self::assertEquals(new \DateTimeImmutable('2026-05-20T10:00:00+00:00'), $result['publicationDate']);
        self::assertEquals(new \DateTimeImmutable('2026-06-01T18:00:00+02:00'), $result['eventDate']);

        $include = $result['audience']['include'];
        self::assertSame(['department:75', 'region:11'], $include['zones']);
        self::assertSame([1, 2, 3], $include['adherent_ids']);
        self::assertSame(['committee-uuid'], $include['committees']);
        self::assertArrayNotHasKey('national', $include);
        self::assertArrayNotHasKey('agoras', $include);
        self::assertArrayNotHasKey('exclude', $result['audience']);
    }

    public function testPollEventDateIsItsClosingDateNotItsOpeningDate(): void
    {
        $result = $this->transformer->transform([
            'type' => 'poll',
            'date' => '2026-07-10T14:00:00+02:00',
            'begin_at' => '2026-07-10T14:00:00+02:00',
            'finish_at' => '2026-07-12T18:00:00+02:00',
            'audience' => $this->defaultFacets(),
        ]);

        self::assertEquals(new \DateTimeImmutable('2026-07-12T18:00:00+02:00'), $result['eventDate']);
        self::assertEquals(new \DateTimeImmutable('2026-07-10T14:00:00+02:00'), $result['publicationDate']);
    }

    public function testNationalNewsBucketsNationalAndZonesWithoutAdherentIds(): void
    {
        $result = $this->transformer->transform([
            'type' => 'news',
            'date' => '2026-05-20T10:00:00+00:00',
            'is_national' => true,
            'zone_codes' => ['region_11'],
            'audience' => $this->defaultFacets(),
        ]);

        $include = $result['audience']['include'];
        self::assertTrue($include['national']);
        self::assertSame(['region:11'], $include['zones']);
        self::assertArrayNotHasKey('adherent_ids', $include);
    }

    public function testPublicationFacetsAreParsedIntoIncludeAndExclude(): void
    {
        $result = $this->transformer->transform([
            'type' => 'publication',
            'date' => '2026-05-20T10:00:00+00:00',
            'is_national' => false,
            'zone_codes' => null,
            'adherent_ids' => null,
            'committee_uuid' => null,
            'agora_uuid' => null,
            'audience' => array_merge($this->defaultFacets(), [
                'age_min' => 18,
                'age_max' => 35,
                'committee_member' => 1,
                'first_membership_since' => 1704067200, // 2024-01-01T00:00:00Z
                'include' => ['tag:jeune', 'zone:department:75', 'gender:male', 'scope_targets:referent:*'],
                'exclude' => ['tag:senior', 'mandate_type:maire'],
            ]),
        ]);

        $include = $result['audience']['include'];
        self::assertSame(['jeune'], $include['tags']);
        self::assertSame(['department:75'], $include['zones']);
        self::assertSame('male', $include['civility']);
        self::assertSame(18, $include['age_min']);
        self::assertSame(35, $include['age_max']);
        self::assertSame(1, $include['committee_member']);
        self::assertSame('2024-01-01T00:00:00Z', $include['first_membership_since']);
        // scope target value keeps its multi-segment shape (split on the first colon only).
        self::assertSame(['referent:*'], $include['scope_targets']);

        $exclude = $result['audience']['exclude'];
        self::assertSame(['senior'], $exclude['tags']);
        self::assertSame(['maire'], $exclude['mandate_types']);
    }

    public function testMultiWordZoneTypeIsSplitOnTheLongestKnownPrefix(): void
    {
        $result = $this->transformer->transform([
            'type' => 'news',
            'date' => '2026-05-20T10:00:00+00:00',
            'zone_codes' => ['city_community_69123', 'department_75'],
            'audience' => $this->defaultFacets(),
        ]);

        // "city_community" must not be split into "city:community_69123".
        self::assertSame(['city_community:69123', 'department:75'], $result['audience']['include']['zones']);
    }

    public function testCommitteeMemberSentinelTwoIsOmitted(): void
    {
        $result = $this->transformer->transform([
            'type' => 'publication',
            'date' => '2026-05-20T10:00:00+00:00',
            'audience' => $this->defaultFacets(), // committee_member => 2
        ]);

        self::assertNull($result['audience'], 'No real constraint => audience is null.');
    }

    public function testNullDateFallsBackToNonNullPublicationDate(): void
    {
        $before = new \DateTimeImmutable();

        $result = $this->transformer->transform([
            'type' => 'news',
            'date' => null,
            'audience' => $this->defaultFacets(),
        ]);

        self::assertInstanceOf(\DateTimeImmutable::class, $result['publicationDate']);
        // Fallback is "now", not an arbitrary date.
        self::assertGreaterThanOrEqual($before, $result['publicationDate']);
        self::assertNull($result['eventDate']);
    }

    public function testDenormalizedExposureFieldsAreProjectedFromDisplay(): void
    {
        $result = $this->transformer->transform([
            'type' => 'event',
            'date' => '2026-05-20T10:00:00+00:00',
            'visibility' => 'public',
            'committee_uuid' => 'committee-1',
            'agora_uuid' => null,
            'audience' => $this->defaultFacets(),
        ]);

        self::assertSame('public', $result['visibility']);
        self::assertSame('committee-1', $result['committeeUuid']);
        self::assertNull($result['agoraUuid']);
    }

    public function testDenormalizedExposureFieldsDefaultToNullWhenAbsent(): void
    {
        $result = $this->transformer->transform([
            'type' => 'social_network_post',
            'date' => '2026-05-20T10:00:00+00:00',
            'audience' => $this->defaultFacets(),
        ]);

        self::assertNull($result['visibility']);
        self::assertNull($result['committeeUuid']);
        self::assertNull($result['agoraUuid']);
    }

    public function testDisplayIsProjectedOnTheAppContract(): void
    {
        $record = [
            'type' => 'news',
            'objectID' => 'news-1',
            'title' => 'Hello',
            'media' => ['type' => 'photo_carousel', 'network' => 'instagram'],
            'access' => ['author_id' => 7, 'team_owner_id' => null],
            'date' => '2026-05-20T10:00:00+00:00',
            // Targeting the normalizer carries alongside the display fields: it is what the audience
            // column is derived from, and it must not survive in the display contract.
            '_tags' => ['news'],
            'adherent_ids' => [7, 42],
            'audience' => $this->defaultFacets(),
        ];

        $result = $this->transformer->transform($record);

        self::assertSame([
            'type' => 'news',
            'objectID' => 'news-1',
            'title' => 'Hello',
            'media' => ['type' => 'photo_carousel', 'network' => 'instagram'],
            'access' => ['author_id' => 7, 'team_owner_id' => null],
            'date' => '2026-05-20T10:00:00+00:00',
        ], $result['display']);
    }

    public function testDisplayProjectionDoesNotLoseTheTargeting(): void
    {
        $result = $this->transformer->transform([
            'type' => 'news',
            'objectID' => 'news-1',
            'title' => 'Hello',
            'date' => '2026-05-20T10:00:00+00:00',
            'adherent_ids' => [7, 42],
            'audience' => $this->defaultFacets(),
        ]);

        // Dropped from the display, still resolved into the audience column the indexer matches on.
        self::assertSame([7, 42], $result['audience']['include']['adherent_ids']);
    }

    /**
     * The default audience block produced by AbstractJeMengageTimelineFeedNormalizer::getAudience()
     * (all sentinels off, committee_member = 2). Non-publication items always carry this shape.
     *
     * @return array<string, mixed>
     */
    private function defaultFacets(): array
    {
        return [
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
        ];
    }
}
