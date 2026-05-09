<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Handler;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\Handler\RetrySendMailchimpCampaignCommandHandler;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class RetrySendMailchimpCampaignCommandHandlerTest extends TestCase
{
    private MockObject&MailchimpCampaignRepository $repository;
    private MockObject&Manager $manager;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&MessageBusInterface $bus;
    private MockObject&LoggerInterface $logger;
    private RetrySendMailchimpCampaignCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MailchimpCampaignRepository::class);
        $this->manager = $this->createMock(Manager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new RetrySendMailchimpCampaignCommandHandler(
            $this->repository,
            $this->manager,
            $this->entityManager,
            $this->bus,
            $this->logger
        );
    }

    public function testHandlerDoesNothingWhenCampaignNotFound(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn(null)
        ;

        $this->manager->expects(self::never())->method('retrySendCampaign');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');
        $this->logger->expects(self::never())->method('info');
        $this->logger->expects(self::never())->method('warning');
        $this->logger->expects(self::never())->method('error');

        ($this->handler)($command);
    }

    public function testHandlerSucceedsOnFirstRetry(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 0);

        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($campaign)
        ;

        $this->manager
            ->expects(self::once())
            ->method('retrySendCampaign')
            ->with($campaign)
            ->willReturn(true)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with('[Mailchimp] Campaign retry succeeded', [
                'campaignId' => 123,
                'retryCount' => 1,
            ])
        ;

        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)($command);

        self::assertSame(1, $campaign->getRetryCount());

        // Verify retry history recorded
        $history = $campaign->getRetryHistory();
        self::assertCount(1, $history);
        self::assertTrue($history[0]['success']);
        self::assertNull($history[0]['detail']);
    }

    public function testHandlerReschedulesOnFailure(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 0);

        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($campaign)
        ;

        $this->manager
            ->expects(self::once())
            ->method('retrySendCampaign')
            ->with($campaign)
            ->willReturn(false)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(function (RetrySendMailchimpCampaignCommand $cmd) {
                    return 123 === $cmd->campaignId && 1 === $cmd->countRetry;
                }),
                self::callback(function (array $stamps) {
                    return 1 === \count($stamps)
                        && $stamps[0] instanceof DelayStamp
                        && 30_000 === $stamps[0]->getDelay();
                })
            )
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('[Mailchimp] Campaign retry scheduled', [
                'campaignId' => 123,
                'retryCount' => 1,
                'delayMs' => 30_000,
            ])
        ;

        ($this->handler)($command);

        // Verify retry history recorded
        $history = $campaign->getRetryHistory();
        self::assertCount(1, $history);
        self::assertFalse($history[0]['success']);
    }

    public static function provideProgressiveDelays(): iterable
    {
        yield 'retry 0 -> 30s delay' => [0, 30_000];
        yield 'retry 1 -> 1min delay' => [1, 60_000];
        yield 'retry 2 -> 5min delay' => [2, 300_000];
        yield 'retry 3 -> 10min delay' => [3, 600_000];
        yield 'retry 4 -> 30min delay' => [4, 1_800_000];
        yield 'retry 5 -> 60min delay' => [5, 3_600_000];
    }

    #[DataProvider('provideProgressiveDelays')]
    public function testHandlerUsesProgressiveDelays(int $retryIndex, int $expectedDelay): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, $retryIndex);

        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->willReturn($campaign)
        ;

        $this->manager
            ->expects(self::once())
            ->method('retrySendCampaign')
            ->willReturn(false)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $capturedDelay = null;
        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->willReturnCallback(function ($cmd, array $stamps) use (&$capturedDelay) {
                $capturedDelay = $stamps[0]->getDelay();

                return new Envelope(new \stdClass());
            })
        ;

        $this->logger
            ->expects(self::once())
            ->method('error')
        ;

        ($this->handler)($command);

        self::assertSame($expectedDelay, $capturedDelay, "Failed for retry index {$retryIndex}");
    }

    public function testHandlerLogsExhaustedAndThrowsAfterMaxRetries(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 6);

        $messageUuid = \Ramsey\Uuid\Uuid::uuid4();
        $message = $this->createMock(AdherentMessageInterface::class);
        $message->method('getUuid')->willReturn($messageUuid);

        $campaign = new MailchimpCampaign($message);
        $campaign->setExternalId('ext_123');
        $campaign->setStaticSegmentId(555);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($campaign)
        ;

        $this->manager
            ->expects(self::once())
            ->method('retrySendCampaign')
            ->with($campaign)
            ->willReturnCallback(function (MailchimpCampaign $c): bool {
                $c->markAsError('mailchimp 500 internal error');

                return false;
            })
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $this->bus->expects(self::never())->method('dispatch');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                '[Mailchimp] Campaign retry exhausted',
                self::callback(function (array $context) use ($messageUuid): bool {
                    return 123 === $context['campaignId']
                        && 'ext_123' === $context['externalId']
                        && 555 === $context['staticSegmentId']
                        && $messageUuid->toString() === $context['messageUuid']
                        && 'mailchimp 500 internal error' === $context['lastError']
                        && 1 === $context['retryCount'];
                }),
            )
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Mailchimp campaign 123 retry exhausted after 1 attempts \(external_id=ext_123\)/');

        ($this->handler)($command);
    }

    public function testHandlerIncrementsRetryCountOnCampaign(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 2);

        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);

        self::assertSame(0, $campaign->getRetryCount());

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->willReturn($campaign)
        ;

        $this->manager
            ->expects(self::once())
            ->method('retrySendCampaign')
            ->willReturn(true)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $this->bus->expects(self::never())->method('dispatch');

        $this->logger
            ->expects(self::once())
            ->method('info')
        ;

        ($this->handler)($command);

        self::assertSame(1, $campaign->getRetryCount());
    }
}
