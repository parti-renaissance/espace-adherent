<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\CapacityAwareMessageInterface;
use App\Video\Transcoding\Command\RelaunchVideoTranscodingCommand;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use App\Video\Transcoding\TranscoderCapacityDeferral;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Uid\Uuid;

final class TranscoderCapacityDeferralTest extends TestCase
{
    private const string UUID = '7ebd8666-8059-4bf2-afcd-8d08ade88af3';

    private MessageBusInterface&MockObject $bus;
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface $logger;
    private TranscoderCapacityDeferral $deferral;

    protected function setUp(): void
    {
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->deferral = new TranscoderCapacityDeferral($this->bus, $this->entityManager, $this->logger);
    }

    public function testDefersWithIncrementedCounterAndDelayBelowCap(): void
    {
        $video = new Video(Uuid::fromString(self::UUID)); // PENDING, never started
        $message = new RelaunchVideoTranscodingCommand(self::UUID, 0);

        $this->entityManager->expects(self::never())->method('flush');
        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(static fn (CapacityAwareMessageInterface $m): bool => 1 === $m->getCapacityAttempt()),
                self::callback(static fn (array $stamps): bool => 1 === \count($stamps)
                    && $stamps[0] instanceof DelayStamp
                    && TranscoderCapacityDeferral::DEFERRAL_DELAY_MS === $stamps[0]->getDelay()),
            )
            ->willReturn(new Envelope(new \stdClass()));

        $this->deferral->deferOrFail($message, $video, TranscoderAtCapacityException::proactive(15, 15));

        // Deferral leaves the video PENDING (and unflushed) so the orphan recovery's age filter stays valid.
        self::assertSame(VideoStatusEnum::PENDING, $video->status);
    }

    public function testMarksFailedAtCapWithoutDispatch(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $video->status = VideoStatusEnum::PROCESSING;
        $video->transcodingStartedAt = new \DateTimeImmutable('-5 minutes');
        $message = new RelaunchVideoTranscodingCommand(self::UUID, TranscoderCapacityDeferral::MAX_ATTEMPTS);

        $this->bus->expects(self::never())->method('dispatch');
        $this->entityManager->expects(self::once())->method('flush');

        $this->deferral->deferOrFail($message, $video, TranscoderAtCapacityException::reactive());

        self::assertSame(VideoStatusEnum::FAILED, $video->status);
        self::assertStringContainsString('gave up', (string) $video->failureReason);
        self::assertInstanceOf(\DateTimeImmutable::class, $video->transcodingFinishedAt);
    }

    public function testGiveUpKeepsFinishedAtNullWhenNeverStarted(): void
    {
        $video = new Video(Uuid::fromString(self::UUID)); // PENDING, transcodingStartedAt null
        $message = new RelaunchVideoTranscodingCommand(self::UUID, TranscoderCapacityDeferral::MAX_ATTEMPTS);

        $this->bus->expects(self::never())->method('dispatch');
        $this->entityManager->expects(self::once())->method('flush');

        $this->deferral->deferOrFail($message, $video, TranscoderAtCapacityException::reactive());

        self::assertSame(VideoStatusEnum::FAILED, $video->status);
        self::assertNull($video->transcodingFinishedAt);
    }
}
