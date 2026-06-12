<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Timeline\CandidateSelection;

use App\JeMengage\Timeline\CandidateSelection\AudienceContext;
use App\JeMengage\Timeline\CandidateSelection\AudienceMatcher;
use App\JeMengage\Timeline\CandidateSelection\RequestFilterCondition;
use App\JeMengage\Timeline\CandidateSelection\TimelineRequestFilter;
use App\JeMengage\Timeline\Indexer\UserProfile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Normative coverage of DESIGN.md § Spec de matching, rules 1-5 + the fail-closed schema guard.
 * Reference user: id 42, tag "adherent:a_jour:x", assembly department 75 + direct city 75056 (reach),
 * deep zones city 75056 / department 75 / region 11 / country FR / district 75-1, committee "cmt-1111".
 */
final class AudienceMatcherTest extends TestCase
{
    #[DataProvider('provideRule1BaseClauseCases')]
    #[DataProvider('provideRule2IncludeTagsCases')]
    #[DataProvider('provideRule3ExcludeTagsCases')]
    #[DataProvider('provideRule4PublicationZonesCases')]
    #[DataProvider('provideRule5CommitteeCases')]
    #[DataProvider('provideSchemaGuardCases')]
    public function testMatches(string $type, ?array $audience, bool $expected): void
    {
        $matcher = new AudienceMatcher(new NullLogger());

        self::assertSame($expected, $matcher->matches(self::context(), $type, $audience));
    }

    public static function provideRule1BaseClauseCases(): iterable
    {
        yield 'publication with no audience passes the base clause' => ['publication', null, true];
        yield 'news without any reach grant is rejected' => ['news', null, false];
        yield 'national news passes' => ['news', ['include' => ['national' => true]], true];
        yield 'news targeting the user id passes' => ['news', ['include' => ['adherent_ids' => [42]]], true];
        yield 'news targeting another id is rejected' => ['news', ['include' => ['adherent_ids' => [43]]], false];
        yield 'news targeting the user id as string is rejected (strict int match)' => ['news', ['include' => ['adherent_ids' => ['42']]], false];
        yield 'event reaching the direct city passes' => ['event', ['include' => ['zones' => ['city:75056']]], true];
        yield 'event reaching a deep-only zone is rejected (region is not assembly/city reach)' => ['event', ['include' => ['zones' => ['region:11']]], false];
        yield 'event reaching only an agora is rejected (agora is not a base clause grant)' => ['event', ['include' => ['agoras' => ['agora-1']]], false];
        yield 'event reaching only a committee is rejected (committee is not a base clause grant)' => ['event', ['include' => ['committees' => ['cmt-1111']]], false];
    }

    public static function provideRule2IncludeTagsCases(): iterable
    {
        yield 'publication targeting a prefix of the user tag passes' => ['publication', ['include' => ['tags' => ['adherent:a_jour']]], true];
        yield 'publication targeting the full user tag passes' => ['publication', ['include' => ['tags' => ['adherent:a_jour:x']]], true];
        yield 'publication targeting another tag is rejected' => ['publication', ['include' => ['tags' => ['autre']]], false];
        yield 'tag targeting is ignored on non-publications' => ['news', ['include' => ['national' => true, 'tags' => ['autre']]], true];
    }

    public static function provideRule3ExcludeTagsCases(): iterable
    {
        yield 'excluded tag prefix rejects a national news (all types)' => ['news', ['include' => ['national' => true], 'exclude' => ['tags' => ['adherent']]], false];
        yield 'excluded exact tag rejects a publication' => ['publication', ['exclude' => ['tags' => ['adherent:a_jour:x']]], false];
        yield 'unrelated excluded tag does not reject' => ['publication', ['exclude' => ['tags' => ['autre']]], true];
    }

    public static function provideRule4PublicationZonesCases(): iterable
    {
        yield 'publication targeting the user department passes' => ['publication', ['include' => ['zones' => ['department:75']]], true];
        yield 'publication targeting another department is rejected' => ['publication', ['include' => ['zones' => ['department:13']]], false];
        yield 'none sentinel skips its type, the other type decides (pass)' => ['publication', ['include' => ['zones' => ['city:none', 'department:75']]], true];
        yield 'AND across types: one matching type is not enough' => ['publication', ['include' => ['zones' => ['department:75', 'region:99']]], false];
        yield 'zone targeting is ignored on non-publications (reach already granted)' => ['news', ['include' => ['national' => true, 'zones' => ['department:13']]], true];
    }

    public static function provideRule5CommitteeCases(): iterable
    {
        yield 'publication targeting the user committee passes' => ['publication', ['include' => ['committees' => ['cmt-1111']]], true];
        yield 'publication targeting another committee is rejected' => ['publication', ['include' => ['committees' => ['cmt-other']]], false];
        yield 'committee targeting is ignored on non-publications' => ['news', ['include' => ['national' => true, 'committees' => ['cmt-other']]], true];
    }

    public static function provideSchemaGuardCases(): iterable
    {
        yield 'unknown include key is rejected' => ['news', ['include' => ['national' => true, 'foo' => 'bar']], false];
        yield 'unknown exclude key is rejected' => ['publication', ['exclude' => ['zones' => ['department:75']]], false];
        yield 'unknown top-level audience key is rejected' => ['publication', ['foo' => 1], false];
        yield 'malformed include bucket is rejected' => ['publication', ['include' => 'oops'], false];
        yield 'known list key with a non-array value is rejected (no TypeError in the read path)' => ['publication', ['include' => ['tags' => 'oops']], false];
        yield 'known bool key with a non-bool value is rejected' => ['news', ['include' => ['national' => 'true']], false];
        yield 'known int key with a string value is rejected' => ['publication', ['include' => ['age_min' => '30']], false];
        yield 'known string key with an int value is rejected' => ['publication', ['include' => ['civility' => 1]], false];
    }

    #[DataProvider('provideRule6And7AgeCases')]
    #[DataProvider('provideRule8CivilityCases')]
    #[DataProvider('provideRule9CommitteeMemberCases')]
    #[DataProvider('provideRule10And11MandateCases')]
    #[DataProvider('provideRule12DateFacetCases')]
    #[DataProvider('provideRule13ScopeTargetCases')]
    public function testMatchesScalarRules(AudienceContext $context, string $type, ?array $audience, bool $expected): void
    {
        $matcher = new AudienceMatcher(new NullLogger());

        self::assertSame($expected, $matcher->matches($context, $type, $audience));
    }

    public static function provideRule6And7AgeCases(): iterable
    {
        $aged30 = self::context(age: 30);
        $noAge = self::context();

        yield 'age_min equal to the user age passes' => [$aged30, 'publication', ['include' => ['age_min' => 30]], true];
        yield 'age_min above the user age is rejected' => [$aged30, 'publication', ['include' => ['age_min' => 31]], false];
        yield 'age_max equal to the user age passes' => [$aged30, 'publication', ['include' => ['age_max' => 30]], true];
        yield 'age_max below the user age is rejected' => [$aged30, 'publication', ['include' => ['age_max' => 29]], false];
        yield 'both bounds around the user age pass' => [$aged30, 'publication', ['include' => ['age_min' => 18, 'age_max' => 65]], true];
        yield 'null age fails any age_min (fail-closed)' => [$noAge, 'publication', ['include' => ['age_min' => 18]], false];
        yield 'null age passes any age_max (Algolia fail-open quirk)' => [$noAge, 'publication', ['include' => ['age_max' => 65]], true];
    }

    public static function provideRule8CivilityCases(): iterable
    {
        $female = self::context(civility: 'female');

        yield 'matching civility passes' => [$female, 'publication', ['include' => ['civility' => 'female']], true];
        yield 'mismatching civility is rejected' => [$female, 'publication', ['include' => ['civility' => 'male']], false];
        yield 'null user gender never matches a civility constraint (fail-closed)' => [self::context(), 'publication', ['include' => ['civility' => 'female']], false];
        yield 'civility is ignored on non-publications' => [$female, 'news', ['include' => ['national' => true, 'civility' => 'male']], true];
    }

    public static function provideRule9CommitteeMemberCases(): iterable
    {
        // The reference context user IS a committee member (committeeMember = 1).
        yield 'committee_member 1 matches a member' => [self::context(), 'publication', ['include' => ['committee_member' => 1]], true];
        yield 'committee_member 0 rejects a member' => [self::context(), 'publication', ['include' => ['committee_member' => 0]], false];
        yield 'committee_member is ignored on non-publications' => [self::context(), 'news', ['include' => ['national' => true, 'committee_member' => 0]], true];
    }

    public static function provideRule10And11MandateCases(): iterable
    {
        $depute = self::context(mandateTypes: ['depute']);
        $maire = self::context(declaredMandates: ['maire']);

        yield 'matching mandate_type include passes' => [$depute, 'publication', ['include' => ['mandate_types' => ['depute']]], true];
        yield 'mismatching mandate_type include is rejected' => [$depute, 'publication', ['include' => ['mandate_types' => ['senateur']]], false];
        yield 'mandate_type include is ignored on non-publications' => [$depute, 'news', ['include' => ['national' => true, 'mandate_types' => ['senateur']]], true];
        yield 'mandate_type exclude rejects on ALL types' => [$depute, 'news', ['include' => ['national' => true], 'exclude' => ['mandate_types' => ['depute']]], false];
        yield 'mandate_type exclude rejects a publication' => [$depute, 'publication', ['exclude' => ['mandate_types' => ['depute']]], false];
        yield 'matching declared_mandate include passes' => [$maire, 'publication', ['include' => ['declared_mandates' => ['maire']]], true];
        yield 'mismatching declared_mandate include is rejected' => [$maire, 'publication', ['include' => ['declared_mandates' => ['autre']]], false];
        yield 'declared_mandate exclude rejects on ALL types' => [$maire, 'news', ['include' => ['national' => true], 'exclude' => ['declared_mandates' => ['maire']]], false];
    }

    public static function provideRule12DateFacetCases(): iterable
    {
        $registered2025 = self::context(registeredDate: '2025-01-01T00:00:00Z');
        $member2024 = self::context(firstMembershipDate: '2024-06-15T00:00:00Z');

        yield 'registered_since older than the user date passes' => [$registered2025, 'publication', ['include' => ['registered_since' => '2024-01-01T00:00:00Z']], true];
        yield 'registered_since newer than the user date is rejected' => [$registered2025, 'publication', ['include' => ['registered_since' => '2026-01-01T00:00:00Z']], false];
        yield 'registered_before newer than the user date passes' => [$registered2025, 'publication', ['include' => ['registered_before' => '2026-01-01T00:00:00Z']], true];
        yield 'registered_before older than the user date is rejected' => [$registered2025, 'publication', ['include' => ['registered_before' => '2024-01-01T00:00:00Z']], false];
        yield 'date constraint on a user without that date is rejected (fail-closed)' => [self::context(), 'publication', ['include' => ['registered_since' => '2024-01-01T00:00:00Z']], false];
        yield 'first_membership_since older than the user date passes' => [$member2024, 'publication', ['include' => ['first_membership_since' => '2024-01-01T00:00:00Z']], true];
        yield 'first_membership_before older than the user date is rejected' => [$member2024, 'publication', ['include' => ['first_membership_before' => '2024-01-01T00:00:00Z']], false];
        yield 'last_membership_since older than the user date passes' => [self::context(lastMembershipDate: '2026-02-01T00:00:00Z'), 'publication', ['include' => ['last_membership_since' => '2026-01-01T00:00:00Z']], true];
    }

    public static function provideRule13ScopeTargetCases(): iterable
    {
        $referent = self::context(scopeTargets: ['referent', 'referent:*']);

        yield 'matching scope target passes' => [$referent, 'publication', ['include' => ['scope_targets' => ['referent']]], true];
        yield 'matching wildcard scope target passes (exact string match)' => [$referent, 'publication', ['include' => ['scope_targets' => ['referent:*']]], true];
        yield 'mismatching scope target is rejected' => [$referent, 'publication', ['include' => ['scope_targets' => ['autre']]], false];
        yield 'scope targets are ignored on non-publications' => [$referent, 'news', ['include' => ['national' => true, 'scope_targets' => ['autre']]], true];
    }

    #[DataProvider('provideViewFilterCases')]
    public function testMatchesWithViewFilter(string $type, ?array $audience, array $conditions, bool $expected): void
    {
        $matcher = new AudienceMatcher(new NullLogger());
        $filter = new TimelineRequestFilter($conditions);

        self::assertSame($expected, $matcher->matches(self::context(), $type, $audience, $filter));
    }

    public static function provideViewFilterCases(): iterable
    {
        yield 'event reaching the filtered zone passes' => [
            'event',
            ['include' => ['zones' => ['department:75']]],
            [new RequestFilterCondition(RequestFilterCondition::ZONE, 'department:75')],
            true,
        ];
        yield 'publication TARGETING the filtered zone is rejected (targeting is not reach)' => [
            'publication',
            ['include' => ['zones' => ['department:75']]],
            [new RequestFilterCondition(RequestFilterCondition::ZONE, 'department:75')],
            false,
        ];
        yield 'event outside the filtered zone is rejected' => [
            'event',
            ['include' => ['national' => true, 'zones' => ['city:75056']]],
            [new RequestFilterCondition(RequestFilterCondition::ZONE, 'department:13')],
            false,
        ];
        yield 'event of the filtered committee passes' => [
            'event',
            ['include' => ['national' => true, 'committees' => ['cmt-1111']]],
            [new RequestFilterCondition(RequestFilterCondition::COMMITTEE, 'cmt-1111')],
            true,
        ];
        yield 'publication targeting the filtered committee is rejected' => [
            'publication',
            ['include' => ['committees' => ['cmt-1111']]],
            [new RequestFilterCondition(RequestFilterCondition::COMMITTEE, 'cmt-1111')],
            false,
        ];
        yield 'event of the filtered agora passes' => [
            'event',
            ['include' => ['national' => true, 'agoras' => ['agora-1']]],
            [new RequestFilterCondition(RequestFilterCondition::AGORA, 'agora-1')],
            true,
        ];
        yield 'national condition keeps national news' => [
            'news',
            ['include' => ['national' => true]],
            [new RequestFilterCondition(RequestFilterCondition::NATIONAL)],
            true,
        ];
        yield 'national condition rejects publications (never national)' => [
            'publication',
            null,
            [new RequestFilterCondition(RequestFilterCondition::NATIONAL)],
            false,
        ];
        yield 'ANDed conditions: one unsatisfied rejects' => [
            'event',
            ['include' => ['national' => true, 'zones' => ['department:75'], 'committees' => ['cmt-1111']]],
            [
                new RequestFilterCondition(RequestFilterCondition::ZONE, 'department:75'),
                new RequestFilterCondition(RequestFilterCondition::COMMITTEE, 'cmt-other'),
            ],
            false,
        ];
        yield 'ANDed conditions: all satisfied pass' => [
            'event',
            ['include' => ['national' => true, 'zones' => ['department:75'], 'committees' => ['cmt-1111']]],
            [
                new RequestFilterCondition(RequestFilterCondition::ZONE, 'department:75'),
                new RequestFilterCondition(RequestFilterCondition::COMMITTEE, 'cmt-1111'),
            ],
            true,
        ];
        yield 'unknown condition kind is rejected fail-closed' => [
            'news',
            ['include' => ['national' => true]],
            [new RequestFilterCondition('bogus', 'x')],
            false,
        ];
    }

    public function testNullFilterIsStrictlyNeutral(): void
    {
        $matcher = new AudienceMatcher(new NullLogger());
        $audience = ['include' => ['national' => true]];

        self::assertSame(
            $matcher->matches(self::context(), 'news', $audience),
            $matcher->matches(self::context(), 'news', $audience, null)
        );
    }

    public function testUnknownKeysAreLoggedAsWarning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('unknown keys'),
                self::callback(static function (array $logContext): bool {
                    return ['foo'] === array_values($logContext['unknown']['include'] ?? []);
                })
            );

        $matcher = new AudienceMatcher($logger);

        self::assertFalse($matcher->matches(self::context(), 'publication', ['include' => ['foo' => 'bar']]));
    }

    private static function context(
        ?int $age = null,
        ?string $civility = null,
        array $mandateTypes = [],
        array $declaredMandates = [],
        array $scopeTargets = [],
        ?string $firstMembershipDate = null,
        ?string $lastMembershipDate = null,
        ?string $registeredDate = null,
    ): AudienceContext {
        return new AudienceContext(
            new UserProfile(
                42,
                ['adherent:a_jour:x'],
                ['city:75056', 'department:75', 'region:11', 'country:FR', 'district:75-1'],
                ['cmt-1111'],
                [],
                $mandateTypes,
                $declaredMandates,
                1,
                $scopeTargets,
                $civility,
                $age,
                $firstMembershipDate,
                $lastMembershipDate,
                $registeredDate,
            ),
            ['adherent', 'adherent:a_jour', 'adherent:a_jour:x'],
            ['department:75', 'city:75056'],
            [
                'borough' => [],
                'canton' => [],
                'city' => ['75056'],
                'department' => ['75'],
                'region' => ['11'],
                'country' => ['FR'],
                'district' => ['75-1'],
                'foreign_district' => [],
                'custom' => [],
            ],
        );
    }
}
