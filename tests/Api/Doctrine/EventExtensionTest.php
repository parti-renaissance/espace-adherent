<?php

declare(strict_types=1);

namespace Tests\App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Api\Doctrine\EventExtension;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Repository\Event\EventRepository;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

#[Group('unit')]
class EventExtensionTest extends TestCase
{
    private EventExtension $extension;
    private ScopeGeneratorResolver&MockObject $scopeResolver;

    protected function setUp(): void
    {
        $this->scopeResolver = $this->createMock(ScopeGeneratorResolver::class);
        $security = $this->createMock(Security::class);
        $eventRepository = $this->createMock(EventRepository::class);
        $this->extension = new EventExtension($security, $this->scopeResolver, $eventRepository);
    }

    public function testPrivateContextWithoutScopeBlocksResults(): void
    {
        $this->scopeResolver->method('generate')->willReturn(null);

        $andWhereCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls);

        $this->applyExtension($qb);

        // Should have published=true filter AND 1=0 block
        $this->assertContains('e.published = :true', $andWhereCalls);
        $this->assertContains('1 = 0', $andWhereCalls);
    }

    public function testPrivateContextWithCommitteeUuidsFiltersCorrectly(): void
    {
        $scope = $this->createScope(
            committeeUuids: ['uuid-committee-1', 'uuid-committee-2'],
            agoraUuids: [],
            zones: [],
            isNational: false
        );
        $this->scopeResolver->method('generate')->willReturn($scope);

        $andWhereCalls = [];
        $setParameterCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls, $setParameterCalls);

        $this->applyExtension($qb);

        // Should have a subquery filter containing committee_uuids
        $hasSubqueryFilter = false;
        foreach ($andWhereCalls as $call) {
            if (str_contains($call, '.id IN (')) {
                $hasSubqueryFilter = true;
                break;
            }
        }
        $this->assertTrue($hasSubqueryFilter, 'Expected subquery filter for committee');
        $this->assertArrayHasKey('committee_uuids', $setParameterCalls);
        $this->assertSame(['uuid-committee-1', 'uuid-committee-2'], $setParameterCalls['committee_uuids']);
    }

    public function testPrivateContextWithAgoraUuidsFiltersCorrectly(): void
    {
        $scope = $this->createScope(
            committeeUuids: [],
            agoraUuids: ['agora-uuid-1'],
            zones: [],
            isNational: false
        );
        $this->scopeResolver->method('generate')->willReturn($scope);

        $andWhereCalls = [];
        $setParameterCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls, $setParameterCalls);

        $this->applyExtension($qb);

        // Should have a subquery filter containing agora_uuids
        $hasSubqueryFilter = false;
        foreach ($andWhereCalls as $call) {
            if (str_contains($call, '.id IN (')) {
                $hasSubqueryFilter = true;
                break;
            }
        }
        $this->assertTrue($hasSubqueryFilter, 'Expected subquery filter for agora');
        $this->assertArrayHasKey('agora_uuids', $setParameterCalls);
        $this->assertSame(['agora-uuid-1'], $setParameterCalls['agora_uuids']);
    }

    public function testPrivateContextWithZonesDelegatesToInZoneOfScopeFilter(): void
    {
        $zone = $this->createMock(Zone::class);
        $zone->method('getId')->willReturn(75);

        $scope = $this->createScope(
            committeeUuids: [],
            agoraUuids: [],
            zones: [$zone],
            isNational: false
        );
        $this->scopeResolver->method('generate')->willReturn($scope);

        $andWhereCalls = [];
        $setParameterCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls, $setParameterCalls);

        $this->applyExtension($qb);

        // Zone filtering is delegated to InZoneOfScopeFilter, so EventExtension
        // should only add the published filter — no zone subquery, no 1=0 block
        $this->assertContains('e.published = :true', $andWhereCalls);
        $this->assertNotContains('1 = 0', $andWhereCalls);
        $this->assertArrayNotHasKey('scope_zone_ids', $setParameterCalls);
    }

    public function testNationalScopeDoesNotFilterByZone(): void
    {
        $scope = $this->createScope(
            committeeUuids: [],
            agoraUuids: [],
            zones: [],
            isNational: true
        );
        $this->scopeResolver->method('generate')->willReturn($scope);

        $andWhereCalls = [];
        $setParameterCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls, $setParameterCalls);

        $this->applyExtension($qb);

        // National scope should only have the published filter, no blocking or scope filters
        $this->assertContains('e.published = :true', $andWhereCalls);
        $this->assertNotContains('1 = 0', $andWhereCalls);
        $this->assertArrayNotHasKey('scope_zone_ids', $setParameterCalls);
        $this->assertArrayNotHasKey('committee_uuids', $setParameterCalls);
        $this->assertArrayNotHasKey('agora_uuids', $setParameterCalls);
    }

    public function testPrivateContextWithNoFiltersBlocksResults(): void
    {
        // Scope without committee, agora, zones and not national should block
        $scope = $this->createScope(
            committeeUuids: [],
            agoraUuids: [],
            zones: [],
            isNational: false
        );
        $this->scopeResolver->method('generate')->willReturn($scope);

        $andWhereCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls);

        $this->applyExtension($qb);

        // Should block with 1=0
        $this->assertContains('1 = 0', $andWhereCalls);
    }

    private function createScope(array $committeeUuids, array $agoraUuids, array $zones, bool $isNational): Scope
    {
        $adherent = $this->createMock(Adherent::class);

        $scope = new Scope(
            $isNational ? 'national' : 'deputy',
            'Test Scope',
            'ROLE_TEST',
            $zones,
            [],
            [],
            $adherent
        );

        // Add attributes for committee and agora UUIDs
        if ($committeeUuids) {
            $committees = array_map(function (string $uuid) {
                return ['uuid' => $uuid, 'name' => 'Committee '.$uuid];
            }, $committeeUuids);
            $scope->addAttribute('committees', $committees);
        }

        if ($agoraUuids) {
            $agoras = array_map(function (string $uuid) {
                return ['uuid' => $uuid, 'name' => 'Agora '.$uuid];
            }, $agoraUuids);
            $scope->addAttribute('agoras', $agoras);
        }

        return $scope;
    }

    private function createQueryBuilderMock(array &$andWhereCalls = [], array &$setParameterCalls = []): QueryBuilder&MockObject
    {
        $subQb = $this->createMock(QueryBuilder::class);
        $subQb->method('select')->willReturnSelf();
        $subQb->method('from')->willReturnSelf();
        $subQb->method('innerJoin')->willReturnSelf();
        $subQb->method('leftJoin')->willReturnSelf();
        $subQb->method('where')->willReturnSelf();
        $subQb->method('andWhere')->willReturnSelf();
        $subQb->method('getDQL')->willReturn('SELECT 1 FROM Event e');

        $em = $this->createMock(EntityManager::class);
        $em->method('createQueryBuilder')->willReturn($subQb);

        $expr = $this->createMock(\Doctrine\ORM\Query\Expr::class);
        $expr->method('exists')->willReturnCallback(fn (string $dql) => "EXISTS($dql)");

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getRootAliases')->willReturn(['e']);
        $qb->method('expr')->willReturn($expr);
        $qb->method('andWhere')->willReturnCallback(function (string $condition) use (&$andWhereCalls, $qb) {
            $andWhereCalls[] = $condition;

            return $qb;
        });
        $qb->method('setParameter')->willReturnCallback(function (string $key, mixed $value) use (&$setParameterCalls, $qb) {
            $setParameterCalls[$key] = $value;

            return $qb;
        });
        $qb->method('getEntityManager')->willReturn($em);

        return $qb;
    }

    private function applyExtension(QueryBuilder $qb): void
    {
        $this->extension->applyToCollection(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            Event::class,
            null,
            [PrivatePublicContextBuilder::CONTEXT_KEY => PrivatePublicContextBuilder::CONTEXT_PRIVATE]
        );
    }
}
