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
 * Targeted unit tests for findAdherentEmailsForMessage (Phase 2).
 *
 * We mock the DBAL Connection. The tested logic:
 * - Iteration by fixed-size chunks (correct yield)
 * - Natural stop when the last chunk is smaller than chunkSize
 * - No-audience case (invalid filter) → return early
 */
class AdherentRepositoryFindEmailsTest extends TestCase
{
    public function testFindAdherentEmailsForMessageWithoutFilterYieldsNothing(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn(null);

        $repository = $this->createRepositoryWithConnection($connection = $this->createMock(Connection::class));
        $connection->expects(self::never())->method('fetchFirstColumn');

        $emails = iterator_to_array($repository->findAdherentEmailsForMessage($message), false);

        self::assertSame([], $emails);
    }

    public function testFindAdherentEmailsForMessageNonNationalScopeWithoutZonesYieldsNothing(): void
    {
        $filter = new AdherentMessageFilter([]);
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getInstanceScope')->willReturn(ScopeEnum::DEPUTY);

        $repository = $this->createRepositoryWithConnection($connection = $this->createMock(Connection::class));
        $connection->expects(self::never())->method('fetchFirstColumn');

        $emails = iterator_to_array($repository->findAdherentEmailsForMessage($message), false);

        self::assertSame([], $emails);
    }

    public function testFindAdherentEmailsForMessagePaginatesUntilLastIncompleteChunk(): void
    {
        $filter = new AdherentMessageFilter([]);
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getInstanceScope')->willReturn(ScopeEnum::NATIONAL);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::exactly(3))
            ->method('fetchFirstColumn')
            ->willReturnOnConsecutiveCalls(
                ['a@b.com', 'c@d.com'], // chunk 1 — full (size=2)
                ['e@f.com', 'g@h.com'], // chunk 2 — full (size=2)
                ['i@j.com'],            // chunk 3 — partial → stop
            );

        $repository = $this->createRepositoryWithConnection($connection);

        $emails = iterator_to_array($repository->findAdherentEmailsForMessage($message, chunkSize: 2), false);

        self::assertSame(['a@b.com', 'c@d.com', 'e@f.com', 'g@h.com', 'i@j.com'], $emails);
    }

    public function testFindAdherentEmailsForMessageEmptyFirstChunkStopsImmediately(): void
    {
        $filter = new AdherentMessageFilter([]);
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFilter')->willReturn($filter);
        $message->method('getInstanceScope')->willReturn(ScopeEnum::NATIONAL);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('fetchFirstColumn')
            ->willReturn([]); // immediate stop

        $repository = $this->createRepositoryWithConnection($connection);

        $emails = iterator_to_array($repository->findAdherentEmailsForMessage($message, chunkSize: 100), false);

        self::assertSame([], $emails);
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
