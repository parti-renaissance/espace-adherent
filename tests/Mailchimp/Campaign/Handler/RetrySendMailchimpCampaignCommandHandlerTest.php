<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\Handler\RetrySendMailchimpCampaignCommandHandler;
use App\Mailchimp\Campaign\MailchimpCampaignSendGuard;
use App\Mailchimp\Campaign\SendDecision;
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
use Symfony\Component\Uid\Uuid;

class RetrySendMailchimpCampaignCommandHandlerTest extends TestCase
{
    private MockObject&MailchimpCampaignRepository $repository;
    private MockObject&Manager $manager;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&MailchimpCampaignSendGuard $sendGuard;
    private MockObject&MessageBusInterface $bus;
    private MockObject&LoggerInterface $logger;
    private RetrySendMailchimpCampaignCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MailchimpCampaignRepository::class);
        $this->manager = $this->createMock(Manager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sendGuard = $this->createMock(MailchimpCampaignSendGuard::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new RetrySendMailchimpCampaignCommandHandler(
            $this->repository,
            $this->manager,
            $this->entityManager,
            $this->sendGuard,
            $this->bus,
            $this->logger,
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

        $this->entityManager->expects(self::never())->method('refresh');
        $this->sendGuard->expects(self::never())->method('evaluate');
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

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::send(93))
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
        self::assertSame(93, $campaign->getRecipientCount(), 'Send decision must persist the validated recipient count.');

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

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::send(93))
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
            ->method('warning')
            ->with('[Mailchimp] Campaign retry scheduled', [
                'campaignId' => 123,
                'retryCount' => 1,
                'delayMs' => 30_000,
            ])
        ;

        ($this->handler)($command);

        $history = $campaign->getRetryHistory();
        self::assertCount(1, $history);
        self::assertFalse($history[0]['success']);
    }

    public static function provideProgressiveDelays(): iterable
    {
        yield 'retry 0 -> 30s' => [0, 30_000];
        yield 'retry 1 -> 1min' => [1, 60_000];
        yield 'retry 2 -> 1.5min' => [2, 90_000];
        yield 'retry 3 -> 2min' => [3, 120_000];
        yield 'retry 4 -> 3min' => [4, 180_000];
        yield 'retry 5 -> 4min' => [5, 240_000];
        yield 'retry 6 -> 5min' => [6, 300_000];
        yield 'retry 7 -> 10min' => [7, 600_000];
        yield 'retry 8 -> 20min' => [8, 1_200_000];
        yield 'retry 9 -> 30min' => [9, 1_800_000];
        yield 'retry 10 -> 60min' => [10, 3_600_000];
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

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::send(1))
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
            ->method('warning')
        ;

        ($this->handler)($command);

        self::assertSame($expectedDelay, $capturedDelay, "Failed for retry index {$retryIndex}");
    }

    public function testHandlerLogsExhaustedWithoutThrowingAfterMaxRetries(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 11);

        $messageUuid = Uuid::v4();
        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::atLeastOnce())->method('getUuid')->willReturn($messageUuid);

        $campaign = new MailchimpCampaign($message);
        $campaign->setExternalId('ext_123');
        $campaign->setStaticSegmentId(555);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($campaign)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::send(93))
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
                        && $messageUuid->toRfc4122() === $context['messageUuid']
                        && 'mailchimp 500 internal error' === $context['lastError']
                        && 1 === $context['retryCount'];
                }),
            )
        ;

        // No throw: avoids Messenger replay which would inflate Sentry with duplicate errors.
        ($this->handler)($command);
    }

    public static function provideSendingOrSentStatus(): iterable
    {
        yield 'Sent' => [MailchimpStatusEnum::Sent];
        yield 'Sending' => [MailchimpStatusEnum::Sending];
    }

    #[DataProvider('provideSendingOrSentStatus')]
    public function testHandlerSkipsWhenCampaignAlreadySentOrSending(MailchimpStatusEnum $status): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 2);

        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);
        $campaign->status = $status;

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($campaign)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard->expects(self::never())->method('evaluate');
        $this->manager->expects(self::never())->method('retrySendCampaign');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');
        $this->logger->expects(self::never())->method('info');
        $this->logger->expects(self::never())->method('warning');
        $this->logger->expects(self::never())->method('error');

        ($this->handler)($command);

        self::assertSame(0, $campaign->getRetryCount(), 'Idempotence guard must NOT increment retry count.');
        self::assertSame([], $campaign->getRetryHistory(), 'Idempotence guard must NOT append to retry history.');
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

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::send(42))
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

    public function testHandlerAbortsOnGuardAbortAndStopsRetryChain(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 2);

        $messageUuid = Uuid::v4();
        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::atLeastOnce())->method('getUuid')->willReturn($messageUuid);

        $campaign = new MailchimpCampaign($message);
        $campaign->setExternalId('ext_123');

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($campaign)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::abort('Recipient overshoot: recipient_count=1200 prepared=93 max=98', 1200))
        ;

        // Manager and retry rescheduling MUST NOT happen for an Abort.
        $this->manager->expects(self::never())->method('retrySendCampaign');
        $this->bus->expects(self::never())->method('dispatch');

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                '[Mailchimp] Campaign send aborted by recipient guard',
                self::callback(function (array $ctx) use ($messageUuid): bool {
                    return 123 === $ctx['campaignId']
                        && 'ext_123' === $ctx['externalId']
                        && str_contains((string) $ctx['reason'], 'overshoot')
                        && 1200 === $ctx['recipientCount']
                        && $messageUuid->toRfc4122() === $ctx['messageUuid'];
                }),
            )
        ;

        ($this->handler)($command);

        self::assertSame(MailchimpStatusEnum::Error, $campaign->status);
        self::assertSame(0, $campaign->getRetryCount(), 'Abort must NOT increment retry count.');

        $history = $campaign->getRetryHistory();
        self::assertCount(1, $history);
        self::assertFalse($history[0]['success']);
        self::assertStringContainsString('overshoot', (string) $history[0]['detail']);
    }

    public function testHandlerReschedulesWhenGuardReturnsRetryAndNotFinalAttempt(): void
    {
        $command = new RetrySendMailchimpCampaignCommand(123, 1);

        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->willReturn($campaign)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::retry('recipient_count not available yet on Mailchimp.'))
        ;

        // Guard said Retry → manager must NOT be called; we just wait and re-attempt later.
        $this->manager->expects(self::never())->method('retrySendCampaign');

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        // countRetry=1 → DELAY_SCHEDULE_MS[1] = 60_000.
        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(function (RetrySendMailchimpCampaignCommand $cmd): bool {
                    return 123 === $cmd->campaignId && 2 === $cmd->countRetry;
                }),
                self::callback(function (array $stamps): bool {
                    return 1 === \count($stamps)
                        && $stamps[0] instanceof DelayStamp
                        && 60_000 === $stamps[0]->getDelay();
                }),
            )
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with('[Mailchimp] Campaign retry scheduled', [
                'campaignId' => 123,
                'retryCount' => 2,
                'delayMs' => 60_000,
            ])
        ;

        ($this->handler)($command);

        self::assertSame(1, $campaign->getRetryCount());
        $history = $campaign->getRetryHistory();
        self::assertCount(1, $history);
        self::assertFalse($history[0]['success']);
        self::assertStringContainsString('recipient_count not available', (string) $history[0]['detail']);
    }

    public function testHandlerAbortsOnFinalAttemptWhenCountUnreadable(): void
    {
        // countRetry=11 → isFinalAttempt=true. An unreadable count (forceSendOnExhaustion=false)
        // must NOT be blind-sent: abort + alert instead of sending to an unverified audience.
        $command = new RetrySendMailchimpCampaignCommand(123, 11);

        $messageUuid = Uuid::v4();
        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::atLeastOnce())->method('getUuid')->willReturn($messageUuid);

        $campaign = new MailchimpCampaign($message);
        $campaign->setExternalId('ext_123');
        $campaign->setStaticSegmentId(555);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->willReturn($campaign)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::retry('recipient_count not readable from Mailchimp.', forceSendOnExhaustion: false))
        ;

        // Unreadable count on exhaustion → never sent, never rescheduled.
        $this->manager->expects(self::never())->method('retrySendCampaign');
        $this->bus->expects(self::never())->method('dispatch');

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                '[Mailchimp] Send aborted after retry exhaustion — recipient_count never confirmed',
                self::callback(function (array $ctx) use ($messageUuid): bool {
                    return 123 === $ctx['campaignId']
                        && 'ext_123' === $ctx['externalId']
                        && str_contains((string) $ctx['reason'], 'not readable')
                        && $messageUuid->toRfc4122() === $ctx['messageUuid'];
                }),
            )
        ;

        ($this->handler)($command);

        self::assertSame(MailchimpStatusEnum::Error, $campaign->status);
        $history = $campaign->getRetryHistory();
        self::assertCount(1, $history);
        self::assertFalse($history[0]['success']);
    }

    public function testHandlerForceSendsReadableUndershootOnFinalAttemptAndPersistsCount(): void
    {
        // countRetry=11 → isFinalAttempt=true. A readable undershoot is force-sendable: send to the
        // available count AND persist it (#5), instead of leaving the DB count stale/null.
        $command = new RetrySendMailchimpCampaignCommand(123, 11);

        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);
        $campaign->setExternalId('ext_123');
        $campaign->setStaticSegmentId(555);

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->willReturn($campaign)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with(self::identicalTo($campaign))
        ;

        $this->sendGuard
            ->expects(self::once())
            ->method('evaluate')
            ->with(self::identicalTo($campaign))
            ->willReturn(SendDecision::retry('Recipient undershoot: recipient_count=247 prepared=1534 min=1457 — segment likely still propagating.', 247, forceSendOnExhaustion: true))
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

        $this->bus->expects(self::never())->method('dispatch');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                '[Mailchimp] recipient_count never settled — sending anyway',
                self::callback(function (array $ctx): bool {
                    return 123 === $ctx['campaignId']
                        && str_contains((string) $ctx['reason'], 'undershoot')
                        && 247 === $ctx['recipientCount'];
                }),
            )
        ;

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with('[Mailchimp] Campaign retry succeeded', [
                'campaignId' => 123,
                'retryCount' => 1,
            ])
        ;

        ($this->handler)($command);

        self::assertSame(1, $campaign->getRetryCount());
        self::assertSame(247, $campaign->getRecipientCount(), 'Force-sent undershoot must persist the count it sent to.');
    }
}
