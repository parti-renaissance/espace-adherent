<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror\Handler;

use App\Entity\Jecoute\News;
use App\JeMengage\Timeline\Indexer\Message\PushTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\Handler\UpsertTimelineFeedCommandHandler;
use App\JeMengage\Timeline\Mirror\Message\UpsertTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\TimelineFeedDocument;
use App\JeMengage\Timeline\Mirror\TimelineFeedResolver;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Uid\Uuid;

class UpsertTimelineFeedCommandHandlerTest extends TestCase
{
    public function testUpsertsResolvedDocumentAndDispatchesPushForPushableType(): void
    {
        $entity = new \stdClass();
        $uuid = Uuid::v4();
        $document = $this->document($uuid, 'news');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->with(News::class, 5)->willReturn($entity);

        $resolver = $this->createMock(TimelineFeedResolver::class);
        $resolver->expects(self::once())->method('resolve')->with($entity)->willReturn($document);

        $writer = $this->createMock(TimelineFeedWriter::class);
        $writer->expects(self::once())->method('upsert')->with($document);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (Envelope $envelope) use ($uuid): bool {
                $message = $envelope->getMessage();

                return $message instanceof PushTimelineFeedCommand
                    && $message->getUuid()->equals($uuid)
                    && null !== $envelope->last(DispatchAfterCurrentBusStamp::class);
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->handler($entityManager, $resolver, $writer, $bus)(new UpsertTimelineFeedCommand(News::class, 5));
    }

    public function testNonPushableTypeIsUpsertedButNotPushed(): void
    {
        $entity = new \stdClass();
        $document = $this->document(Uuid::v4(), 'transactional_message');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->with(News::class, 5)->willReturn($entity);

        $resolver = $this->createMock(TimelineFeedResolver::class);
        $resolver->expects(self::once())->method('resolve')->with($entity)->willReturn($document);

        $writer = $this->createMock(TimelineFeedWriter::class);
        $writer->expects(self::once())->method('upsert')->with($document);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->handler($entityManager, $resolver, $writer, $bus)(new UpsertTimelineFeedCommand(News::class, 5));
    }

    public function testSkipsWhenEntityNotFound(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->with(News::class, 5)->willReturn(null);

        $resolver = $this->createMock(TimelineFeedResolver::class);
        $resolver->expects(self::never())->method('resolve');

        $writer = $this->createMock(TimelineFeedWriter::class);
        $writer->expects(self::never())->method('upsert');
        $writer->expects(self::never())->method('delete');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->handler($entityManager, $resolver, $writer, $bus)(new UpsertTimelineFeedCommand(News::class, 5));
    }

    public function testDeletesWhenEntityNotIndexableAndDoesNotPush(): void
    {
        $entity = new \stdClass();
        $uuid = Uuid::v4();
        $document = new TimelineFeedDocument($uuid, null, null, null, null, null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->with(News::class, 5)->willReturn($entity);

        $resolver = $this->createMock(TimelineFeedResolver::class);
        $resolver->expects(self::once())->method('resolve')->with($entity)->willReturn($document);

        $writer = $this->createMock(TimelineFeedWriter::class);
        $writer->expects(self::once())->method('delete')->with($uuid);
        $writer->expects(self::never())->method('upsert');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->handler($entityManager, $resolver, $writer, $bus)(new UpsertTimelineFeedCommand(News::class, 5));
    }

    public function testNoopWhenUnsupported(): void
    {
        $entity = new \stdClass();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->with(News::class, 5)->willReturn($entity);

        $resolver = $this->createMock(TimelineFeedResolver::class);
        $resolver->expects(self::once())->method('resolve')->with($entity)->willReturn(null);

        $writer = $this->createMock(TimelineFeedWriter::class);
        $writer->expects(self::never())->method('upsert');
        $writer->expects(self::never())->method('delete');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->handler($entityManager, $resolver, $writer, $bus)(new UpsertTimelineFeedCommand(News::class, 5));
    }

    private function document(Uuid $uuid, string $type): TimelineFeedDocument
    {
        return new TimelineFeedDocument($uuid, $type, new \DateTimeImmutable(), null, null, ['objectID' => $uuid->toRfc4122(), 'title' => 'Hello']);
    }

    private function handler(
        EntityManagerInterface $entityManager,
        TimelineFeedResolver $resolver,
        TimelineFeedWriter $writer,
        MessageBusInterface $bus,
    ): UpsertTimelineFeedCommandHandler {
        return new UpsertTimelineFeedCommandHandler($entityManager, $resolver, $writer, $bus);
    }
}
