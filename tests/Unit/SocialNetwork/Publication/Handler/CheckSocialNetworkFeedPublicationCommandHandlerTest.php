<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Publication\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Publication\Command\CheckSocialNetworkFeedPublicationCommand;
use App\SocialNetwork\Publication\Handler\CheckSocialNetworkFeedPublicationCommandHandler;
use App\SocialNetwork\Publication\SocialNetworkFeedReadinessChecker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

final class CheckSocialNetworkFeedPublicationCommandHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private SocialNetworkFeedRepository&MockObject $repository;
    private SocialNetworkFeedReadinessChecker&MockObject $readinessChecker;
    private MessageBusInterface&MockObject $bus;
    private CheckSocialNetworkFeedPublicationCommandHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(SocialNetworkFeedRepository::class);
        $this->readinessChecker = $this->createMock(SocialNetworkFeedReadinessChecker::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->handler = new CheckSocialNetworkFeedPublicationCommandHandler(
            $this->entityManager,
            $this->repository,
            $this->readinessChecker,
            $this->bus,
        );
    }

    public function testDoesNothingWhenFeedNotFound(): void
    {
        $this->repository->expects(self::once())->method('find')->with(1)->willReturn(null);
        $this->readinessChecker->expects(self::never())->method('isReadyToPublish');
        $this->entityManager->expects(self::never())->method('refresh');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));
    }

    public function testStopsWhenAlreadyPublished(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->published = true;

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::never())->method('isReadyToPublish');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));
    }

    public function testPublishesWhenReady(): void
    {
        $feed = new SocialNetworkFeed();

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(true);
        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));

        self::assertTrue($feed->published);
        self::assertInstanceOf(\DateTimeImmutable::class, $feed->publishedAt);
    }

    public function testRedispatchesWhenNotReadyBeforeDeadline(): void
    {
        $feed = new SocialNetworkFeed();

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(false);
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::once())->method('dispatch')
            ->with(
                self::isInstanceOf(CheckSocialNetworkFeedPublicationCommand::class),
                self::callback(static function (array $stamps): bool {
                    return 1 === \count($stamps)
                        && $stamps[0] instanceof DelayStamp
                        && 30000 === $stamps[0]->getDelay();
                }),
            )
            ->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));

        self::assertFalse($feed->published);
    }

    public function testStopsAtDeadlineWhenNotReady(): void
    {
        $feed = new SocialNetworkFeed();

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(false);
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        // startedAt far enough in the past to exceed the 2h deadline.
        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time() - 8000));
    }
}
