<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Publication\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPublicationFailure;
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

    public function testKeepsPublishedWhenStillReady(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        $feed->score = 2;
        $feed->published = true;

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(true);
        $this->readinessChecker->expects(self::never())->method('getBlockingReason');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));

        self::assertTrue($feed->published);
    }

    public function testDemotesWhenPublishedAndNoLongerReadyBeforeDeadline(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        $feed->score = 2;
        $feed->published = true;
        $feed->publishedAt = new \DateTimeImmutable('-1 hour');

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(false);
        $this->readinessChecker->expects(self::never())->method('getBlockingReason');
        $this->entityManager->expects(self::once())->method('flush');
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

        // The post is taken back down so it leaves the index until its media is ready again.
        self::assertFalse($feed->published);
        self::assertNull($feed->publishedAt);
    }

    public function testDemotesAndRecordsFailureWhenPublishedButNotReadyAtDeadline(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        $feed->score = 2;
        $feed->published = true;
        $feed->publishedAt = new \DateTimeImmutable('-3 hours');

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(false);
        $this->readinessChecker->expects(self::once())->method('getBlockingReason')->with($feed)
            ->willReturn(SocialNetworkFeedPublicationFailure::VideoNotTranscoded);
        // A single flush covers both the demotion and the failure record.
        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        // startedAt far enough in the past to exceed the 2h deadline.
        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time() - 8000));

        self::assertFalse($feed->published);
        self::assertNull($feed->publishedAt);
        self::assertSame(SocialNetworkFeedPublicationFailure::VideoNotTranscoded, $feed->publicationFailure);
        self::assertInstanceOf(\DateTimeImmutable::class, $feed->publicationFailedAt);
    }

    public function testDoesNotPublishExcludedPlatform(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'twitter';

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::never())->method('refresh');
        $this->readinessChecker->expects(self::never())->method('isReadyToPublish');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));

        self::assertFalse($feed->published);
    }

    public function testDoesNotPublishWhenScoreBelowMinimum(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        // Below the minimum publishable score of 1.
        $feed->score = 0;

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::never())->method('refresh');
        $this->readinessChecker->expects(self::never())->method('isReadyToPublish');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));

        self::assertFalse($feed->published);
    }

    public function testDoesNotPublishWhenScoreIsNull(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        // No score from the scraper: treated as below the minimum, so the feed is not published.

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::never())->method('refresh');
        $this->readinessChecker->expects(self::never())->method('isReadyToPublish');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));

        self::assertFalse($feed->published);
    }

    public function testPublishesWhenReadyAndClearsStaleFailure(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        $feed->score = 2;
        // A superseded attempt had recorded a failure; publishing must clear it.
        $feed->publicationFailure = SocialNetworkFeedPublicationFailure::VideoNotTranscoded;
        $feed->publicationFailedAt = new \DateTimeImmutable('-1 hour');

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(true);
        $this->readinessChecker->expects(self::never())->method('getBlockingReason');
        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time()));

        self::assertTrue($feed->published);
        self::assertInstanceOf(\DateTimeImmutable::class, $feed->publishedAt);
        self::assertNull($feed->publicationFailure);
        self::assertNull($feed->publicationFailedAt);
    }

    public function testRedispatchesWhenNotReadyBeforeDeadline(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        $feed->score = 2;

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

    public function testRecordsFailureAtDeadlineWhenNotReady(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';
        $feed->score = 2;

        $this->repository->expects(self::once())->method('find')->with(1)->willReturn($feed);
        $this->entityManager->expects(self::once())->method('refresh')->with($feed);
        $this->readinessChecker->expects(self::once())->method('isReadyToPublish')->with($feed)->willReturn(false);
        $this->readinessChecker->expects(self::once())->method('getBlockingReason')->with($feed)
            ->willReturn(SocialNetworkFeedPublicationFailure::VideoNotTranscoded);
        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        // startedAt far enough in the past to exceed the 2h deadline.
        ($this->handler)(new CheckSocialNetworkFeedPublicationCommand(1, time() - 8000));

        self::assertFalse($feed->published);
        self::assertSame(SocialNetworkFeedPublicationFailure::VideoNotTranscoded, $feed->publicationFailure);
        self::assertInstanceOf(\DateTimeImmutable::class, $feed->publicationFailedAt);
    }
}
