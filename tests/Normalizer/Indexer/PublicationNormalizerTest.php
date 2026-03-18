<?php

declare(strict_types=1);

namespace Tests\App\Normalizer\Indexer;

use Algolia\SearchBundle\Searchable;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Normalizer\Indexer\PublicationNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn('test-uuid');

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
}
