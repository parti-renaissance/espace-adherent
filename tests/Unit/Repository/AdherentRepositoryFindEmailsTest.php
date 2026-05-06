<?php

declare(strict_types=1);

namespace Tests\App\Unit\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Targeted unit tests for findAdherentIdsForMessage.
 *
 * We mock the DBAL Connection. The tested logic:
 * - Single fetchFirstColumn call (no pagination)
 * - No-audience cases (invalid filter, non-national without zones) → return early
 * - Result is cast to list<int>
 */
class AdherentRepositoryFindEmailsTest extends TestCase
{
    public function testFindAdherentIdsForMessageWithoutFilterReturnsEmpty(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn(null);

        $repository = $this->createRepositoryWithConnection($connection = $this->createMock(Connection::class));
        $connection->expects(self::never())->method('fetchFirstColumn');

        self::assertSame([], $repository->findAdherentIdsForMessage($message));
    }

    public function testFindAdherentIdsForMessageNonNationalScopeWithoutZonesReturnsEmpty(): void
    {
        $filter = new AdherentMessageFilter([]);
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getInstanceScope')->willReturn(ScopeEnum::DEPUTY);

        $repository = $this->createRepositoryWithConnection($connection = $this->createMock(Connection::class));
        $connection->expects(self::never())->method('fetchFirstColumn');

        self::assertSame([], $repository->findAdherentIdsForMessage($message));
    }

    public function testFindAdherentIdsForMessageReturnsFullResultSet(): void
    {
        $filter = new AdherentMessageFilter([]);
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getInstanceScope')->willReturn(ScopeEnum::NATIONAL);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('fetchFirstColumn')
            ->willReturn([10, 20, 30])
        ;

        $repository = $this->createRepositoryWithConnection($connection);

        self::assertSame([10, 20, 30], $repository->findAdherentIdsForMessage($message));
    }

    public function testFindAdherentIdsForMessageEmptyAudienceReturnsEmpty(): void
    {
        $filter = new AdherentMessageFilter([]);
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getInstanceScope')->willReturn(ScopeEnum::NATIONAL);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('fetchFirstColumn')
            ->willReturn([])
        ;

        $repository = $this->createRepositoryWithConnection($connection);

        self::assertSame([], $repository->findAdherentIdsForMessage($message));
    }

    private function createRepositoryWithConnection(Connection $connection): AdherentRepository
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $classMetadata = new ClassMetadata(Adherent::class);
        $em->method('getClassMetadata')->willReturn($classMetadata);

        $registry = $this->createStub(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return new AdherentRepository($registry);
    }
}
