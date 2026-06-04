<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror;

use App\Algolia\AlgoliaIndexerInterface;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\Jecoute\News;
use App\JeMengage\Timeline\Mirror\Message\DeleteTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\Message\UpsertTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\TimelineFeedDispatchingEntityManager;
use App\JeMengage\Timeline\Mirror\TimelineFeedResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class TimelineFeedDispatchingEntityManagerTest extends TestCase
{
    public function testPostPersistDelegatesToInnerThenDispatchesUpsert(): void
    {
        $entity = $this->createStub(AlgoliaIndexedEntityInterface::class);

        $inner = $this->createMock(AlgoliaIndexerInterface::class);
        $inner->expects(self::once())->method('postPersist')->with($entity);

        $resolver = $this->createStub(TimelineFeedResolver::class);
        $resolver->method('supports')->willReturn(true);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn ($m) => $m instanceof UpsertTimelineFeedCommand && News::class === $m->entityClass && 42 === $m->entityId))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->manager($inner, $bus, $resolver, $this->entityManagerReturning(News::class, 42))->postPersist($entity);
    }

    public function testPostUpdateDispatchesUpsert(): void
    {
        $entity = $this->createStub(AlgoliaIndexedEntityInterface::class);

        $inner = $this->createMock(AlgoliaIndexerInterface::class);
        $inner->expects(self::once())->method('postUpdate')->with($entity);

        $resolver = $this->createStub(TimelineFeedResolver::class);
        $resolver->method('supports')->willReturn(true);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())->method('dispatch')->with(self::isInstanceOf(UpsertTimelineFeedCommand::class))->willReturn(new Envelope(new \stdClass()));

        $this->manager($inner, $bus, $resolver, $this->entityManagerReturning(News::class, 42))->postUpdate($entity);
    }

    public function testPreRemoveDelegatesThenDispatchesDelete(): void
    {
        $entity = $this->createStub(AlgoliaIndexedEntityInterface::class);

        $inner = $this->createMock(AlgoliaIndexerInterface::class);
        $inner->expects(self::once())->method('preRemove')->with($entity);

        $objectId = Uuid::v4();
        $resolver = $this->createStub(TimelineFeedResolver::class);
        $resolver->method('objectId')->willReturn($objectId);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn ($m) => $m instanceof DeleteTimelineFeedCommand && $m->getUuid() === $objectId))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->manager($inner, $bus, $resolver)->preRemove($entity);
    }

    public function testUnsupportedEntityDelegatesButDoesNotDispatch(): void
    {
        $entity = $this->createStub(AlgoliaIndexedEntityInterface::class);

        $inner = $this->createMock(AlgoliaIndexerInterface::class);
        $inner->expects(self::once())->method('postPersist')->with($entity);

        $resolver = $this->createStub(TimelineFeedResolver::class);
        $resolver->method('supports')->willReturn(false);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->manager($inner, $bus, $resolver)->postPersist($entity);
    }

    public function testBatchDelegatesThenDispatchesUpsertPerSupportedEntity(): void
    {
        $entities = [$this->createStub(AlgoliaIndexedEntityInterface::class), $this->createStub(AlgoliaIndexedEntityInterface::class)];

        $inner = $this->createMock(AlgoliaIndexerInterface::class);
        $inner->expects(self::once())->method('batch')->with($entities);

        $resolver = $this->createStub(TimelineFeedResolver::class);
        $resolver->method('supports')->willReturn(true);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::exactly(2))->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $this->manager($inner, $bus, $resolver, $this->entityManagerReturning(News::class, 7))->batch($entities);
    }

    private function manager(
        AlgoliaIndexerInterface $inner,
        MessageBusInterface $bus,
        TimelineFeedResolver $resolver,
        ?EntityManagerInterface $entityManager = null,
    ): TimelineFeedDispatchingEntityManager {
        return new TimelineFeedDispatchingEntityManager(
            $inner,
            $bus,
            $resolver,
            $entityManager ?? $this->entityManagerReturning(News::class, 1),
        );
    }

    private function entityManagerReturning(string $class, int|string $id): EntityManagerInterface
    {
        $metadata = $this->createStub(ClassMetadata::class);
        $metadata->method('getName')->willReturn($class);
        $metadata->method('getIdentifierValues')->willReturn(['id' => $id]);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturn($metadata);

        return $entityManager;
    }
}
