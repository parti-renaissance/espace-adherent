<?php

declare(strict_types=1);

namespace Tests\App\Normalizer\Indexer;

use Algolia\SearchBundle\Searchable;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Normalizer\Indexer\PublicationNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class PublicationNormalizerTest extends TestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private PublicationNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->normalizer = new PublicationNormalizer($this->urlGenerator);
    }

    public function testGetAudienceWithoutScopeTargetsReturnsScopeTargetFalse(): void
    {
        $uuid = Uuid::v4();

        $filter = new AdherentMessageFilter();
        $filter->scopeTargets = null;

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertArrayHasKey('audience', $result);
        self::assertArrayHasKey('scope_targets', $result['audience']);
        self::assertFalse($result['audience']['scope_targets']);

        // Verify no scope_target entries in include (zone entries may exist but no scope_target:*)
        if (isset($result['audience']['include'])) {
            $scopeTargetKeys = array_filter($result['audience']['include'], function (string $key): bool {
                return str_starts_with($key, 'scope_targets:');
            });
            self::assertEmpty($scopeTargetKeys);
        }
    }

    public function testGetAudienceWithEmptyScopeTargetsReturnsScopeTargetFalse(): void
    {
        $uuid = Uuid::v4();

        $filter = new AdherentMessageFilter();
        $filter->scopeTargets = [];

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertArrayHasKey('audience', $result);
        self::assertArrayHasKey('scope_targets', $result['audience']);
        self::assertFalse($result['audience']['scope_targets']);

        // Verify no scope_target entries in include
        if (isset($result['audience']['include'])) {
            $scopeTargetKeys = array_filter($result['audience']['include'], function (string $key): bool {
                return str_starts_with($key, 'scope_targets:');
            });
            self::assertEmpty($scopeTargetKeys);
        }
    }

    public function testGetAudienceWithScopeTargetsReturnsScopeTargetTrueAndIncludeKeys(): void
    {
        $uuid = Uuid::v4();

        $filter = new AdherentMessageFilter();
        $filter->scopeTargets = [
            ['role' => 'deputy', 'include_role' => true, 'include_team' => false],
            ['role' => 'senator', 'include_role' => true, 'include_team' => false],
        ];

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertArrayHasKey('audience', $result);
        self::assertArrayHasKey('scope_targets', $result['audience']);
        self::assertTrue($result['audience']['scope_targets']);
        self::assertArrayHasKey('include', $result['audience']);
        self::assertContains('scope_targets:deputy', $result['audience']['include']);
        self::assertContains('scope_targets:senator', $result['audience']['include']);
    }

    public function testGetAudienceWithScopeTargetsMissingRoleSkipsEntry(): void
    {
        $uuid = Uuid::v4();

        $filter = new AdherentMessageFilter();
        $filter->scopeTargets = [
            ['role' => 'deputy', 'include_role' => true, 'include_team' => false],
            ['include_role' => true, 'include_team' => false], // Missing role
            ['role' => '', 'include_role' => true, 'include_team' => false], // Empty role
            ['role' => null, 'include_role' => true, 'include_team' => false], // Null role
        ];

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertArrayHasKey('audience', $result);
        self::assertTrue($result['audience']['scope_targets']);
        self::assertArrayHasKey('include', $result['audience']);

        // Count only scope_target entries
        $scopeTargetKeys = array_filter($result['audience']['include'], function (string $key): bool {
            return str_starts_with($key, 'scope_targets:');
        });
        self::assertCount(1, $scopeTargetKeys);
        self::assertContains('scope_targets:deputy', $result['audience']['include']);
    }

    public function testGetAudienceWithoutFilterReturnsScopeTargetFalse(): void
    {
        $uuid = Uuid::v4();

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn(null);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertArrayHasKey('audience', $result);
        self::assertArrayHasKey('scope_targets', $result['audience']);
        self::assertFalse($result['audience']['scope_targets']);
    }

    public function testGetAudienceWithScopeTargetsIncludeTeamAddsTeamKeys(): void
    {
        $uuid = Uuid::v4();

        $filter = new AdherentMessageFilter();
        $filter->scopeTargets = [
            [
                'role' => 'president_departmental_assembly',
                'include_role' => true,
                'include_team' => true,
                'team_roles' => ['custom', 'new_members_manager'],
            ],
        ];

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertArrayHasKey('audience', $result);
        self::assertTrue($result['audience']['scope_targets']);
        self::assertArrayHasKey('include', $result['audience']);
        // Direct role
        self::assertContains('scope_targets:president_departmental_assembly', $result['audience']['include']);
        // Team roles
        self::assertContains('scope_targets:president_departmental_assembly:custom', $result['audience']['include']);
        self::assertContains('scope_targets:president_departmental_assembly:new_members_manager', $result['audience']['include']);
    }

    public function testGetAudienceWithScopeTargetsOnlyTeamNoDirectRole(): void
    {
        $uuid = Uuid::v4();

        $filter = new AdherentMessageFilter();
        $filter->scopeTargets = [
            [
                'role' => 'deputy',
                'include_role' => false,
                'include_team' => true,
                'team_roles' => ['head_of_european_affairs'],
            ],
        ];

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertTrue($result['audience']['scope_targets']);
        // No direct role
        self::assertNotContains('scope_targets:deputy', $result['audience']['include']);
        // Team role only
        self::assertContains('scope_targets:deputy:head_of_european_affairs', $result['audience']['include']);
    }

    public function testGetAudienceWithScopeTargetsIncludeAllTeamAddsWildcard(): void
    {
        $uuid = Uuid::v4();

        $filter = new AdherentMessageFilter();
        $filter->scopeTargets = [
            [
                'role' => 'deputy',
                'include_role' => true,
                'include_team' => true,
                // No team_roles = all team members
            ],
        ];

        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn($uuid);
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        $result = $this->normalizer->normalize($message);

        self::assertIsArray($result);
        self::assertTrue($result['audience']['scope_targets']);
        // Direct role
        self::assertContains('scope_targets:deputy', $result['audience']['include']);
        // Wildcard for all team members
        self::assertContains('scope_targets:deputy:*', $result['audience']['include']);
    }

    public function testGetAudienceWithExcludeOnlyTagDoesNotEnableTagFilter(): void
    {
        $filter = new AdherentMessageFilter();
        $filter->electTags = '!elu';

        $result = $this->normalizer->normalize($this->createMessageWithFilter($filter));

        // An exclude-only tag must NOT enable the positive tag filter, otherwise the
        // publication requires a positive include match it can never satisfy and stays
        // invisible to everyone instead of "everyone except the excluded tag".
        self::assertFalse($result['audience']['tag']);
        self::assertArrayHasKey('exclude', $result['audience']);
        self::assertContains('tag:elu', $result['audience']['exclude']);
        self::assertEmpty($this->includeKeysWithPrefix($result, 'tag:'));
    }

    public function testGetAudienceWithIncludeTagEnablesTagFilter(): void
    {
        $filter = new AdherentMessageFilter();
        $filter->adherentTags = 'sympathisant';

        $result = $this->normalizer->normalize($this->createMessageWithFilter($filter));

        self::assertTrue($result['audience']['tag']);
        self::assertContains('tag:sympathisant', $result['audience']['include']);
    }

    public function testGetAudienceWithExcludeOnlyMandateTypeDoesNotEnableMandateFilter(): void
    {
        $filter = new AdherentMessageFilter();
        $filter->setElectMandate('!deputy');

        $result = $this->normalizer->normalize($this->createMessageWithFilter($filter));

        self::assertFalse($result['audience']['mandate_type']);
        self::assertArrayHasKey('exclude', $result['audience']);
        self::assertContains('mandate_type:deputy', $result['audience']['exclude']);
        self::assertEmpty($this->includeKeysWithPrefix($result, 'mandate_type:'));
    }

    public function testGetAudienceWithExcludeOnlyDeclaredMandateDoesNotEnableDeclaredMandateFilter(): void
    {
        $filter = new AdherentMessageFilter();
        $filter->setDeclaredMandate('!mayor');

        $result = $this->normalizer->normalize($this->createMessageWithFilter($filter));

        self::assertFalse($result['audience']['declared_mandate']);
        self::assertArrayHasKey('exclude', $result['audience']);
        self::assertContains('declared_mandate:mayor', $result['audience']['exclude']);
        self::assertEmpty($this->includeKeysWithPrefix($result, 'declared_mandate:'));
    }

    public function testGetAudienceTargetingCommitteeMembersIndexesOne(): void
    {
        $filter = new AdherentMessageFilter();
        $filter->setIsCommitteeMember(true);

        $result = $this->normalizer->normalize($this->createMessageWithFilter($filter));

        // The controller reads the scalar audience.committee_member (2=none, 1=member, 0=non-member).
        self::assertSame(1, $result['audience']['committee_member']);
    }

    public function testGetAudienceTargetingNonCommitteeMembersIndexesZero(): void
    {
        $filter = new AdherentMessageFilter();
        $filter->setIsCommitteeMember(false);

        $result = $this->normalizer->normalize($this->createMessageWithFilter($filter));

        // Must be 0 (not boolean true): otherwise non-members can never match and members
        // wrongly match, inverting the "target non-members" audience.
        self::assertSame(0, $result['audience']['committee_member']);
    }

    public function testGetAudienceWithoutCommitteeMemberTargetingKeepsSentinel(): void
    {
        $filter = new AdherentMessageFilter();

        $result = $this->normalizer->normalize($this->createMessageWithFilter($filter));

        self::assertSame(2, $result['audience']['committee_member']);
    }

    public function testSupportsNormalizationReturnsTrueForAdherentMessage(): void
    {
        $message = $this->createMock(AdherentMessage::class);

        self::assertTrue($this->normalizer->supportsNormalization($message, Searchable::NORMALIZATION_FORMAT));
    }

    public function testSupportsNormalizationReturnsFalseForOtherTypes(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass(), Searchable::NORMALIZATION_FORMAT));
    }

    public function testSupportsNormalizationReturnsFalseWithoutCorrectFormat(): void
    {
        $message = $this->createMock(AdherentMessage::class);

        self::assertFalse($this->normalizer->supportsNormalization($message));
        self::assertFalse($this->normalizer->supportsNormalization($message, 'json'));
    }

    private function createMessageWithFilter(AdherentMessageFilter $filter): AdherentMessage&MockObject
    {
        $message = $this->createMock(AdherentMessage::class);
        $message->method('getUuid')->willReturn(Uuid::v4());
        $message->method('getSubject')->willReturn('Test subject');
        $message->method('getJsonContent')->willReturn('Test content');
        $message->method('getSentAt')->willReturn(new \DateTimeImmutable());
        $message->method('getSender')->willReturn(null);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getAuthor')->willReturn(null);

        return $message;
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return string[]
     */
    private function includeKeysWithPrefix(array $result, string $prefix): array
    {
        return array_filter(
            $result['audience']['include'] ?? [],
            static fn (string $key): bool => str_starts_with($key, $prefix)
        );
    }
}
