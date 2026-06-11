<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Command\VerifyCampaignDeliveryCommand;
use App\Mailchimp\Campaign\Handler\VerifyCampaignDeliveryCommandHandler;
use App\Mailchimp\Campaign\PostSendDeliveryGuard;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class VerifyCampaignDeliveryCommandHandlerTest extends TestCase
{
    private const int FINAL_RETRY = 5; // >= count(DELAY_SCHEDULE_MS)
    private const int FINAL_SENDING_RETRY = 18; // >= SENDING_MAX_POLLS

    public function testInvokeWithMissingCampaignReturnsEarly(): void
    {
        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(99)->willReturn(null);

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('getCampaignStatus');
        $manager->expects(self::never())->method('getReportData');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->buildHandler($repository, $manager, $bus)(new VerifyCampaignDeliveryCommand(99));
    }

    public function testInvokeWithDeliveredCampaignDoesNothing(): void
    {
        $campaign = $this->buildCampaign(preparedCount: 93);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $manager = $this->mockReads($campaign, MailchimpStatusEnum::Sent, ['emails_sent' => 100]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->buildHandler($repository, $manager, $bus)(new VerifyCampaignDeliveryCommand(7));
    }

    public function testInvokeWithZeroDeliveryNotFinalReschedules(): void
    {
        $campaign = $this->buildCampaign(preparedCount: 93);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $manager = $this->mockReads($campaign, MailchimpStatusEnum::Sent, ['emails_sent' => 0]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(fn (VerifyCampaignDeliveryCommand $cmd): bool => 7 === $cmd->campaignId && 1 === $cmd->countRetry),
                self::callback(fn (array $stamps): bool => 1 === \count($stamps) && $stamps[0] instanceof DelayStamp && 180_000 === $stamps[0]->getDelay()),
            )
            ->willReturnCallback(fn (object $cmd): Envelope => new Envelope($cmd));

        $this->buildHandler($repository, $manager, $bus)(new VerifyCampaignDeliveryCommand(7, 0));
    }

    public function testInvokeWithStillSendingPastSendingWindowAlerts(): void
    {
        // The sending window is exhausted (sendingRetry >= SENDING_MAX_POLLS): a campaign stuck in
        // "sending" for hours alerts (logger->error → Sentry), but is never rescheduled further.
        $campaign = $this->buildCampaign(preparedCount: 93);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $manager = $this->mockReads($campaign, MailchimpStatusEnum::Sending, ['emails_sent' => 0]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with('[Mailchimp][PostSendGuard] Still sending at end of window', self::callback('is_array'));

        $this->buildHandler($repository, $manager, $this->silentBus(), $logger)(new VerifyCampaignDeliveryCommand(7, 0, self::FINAL_SENDING_RETRY));
    }

    public function testInvokeWhileSendingPollsSendingWindowAndPreservesConfirmWindow(): void
    {
        // The confirmation window is already exhausted (countRetry = FINAL_RETRY) but the campaign is
        // still "sending": it must keep polling on the SENDING window WITHOUT consuming countRetry, so
        // the ~30 min confirmation window only starts once the campaign reaches a terminal "sent".
        $campaign = $this->buildCampaign(preparedCount: 93);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $manager = $this->mockReads($campaign, MailchimpStatusEnum::Sending, ['emails_sent' => 0]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(fn (VerifyCampaignDeliveryCommand $cmd): bool => 7 === $cmd->campaignId && self::FINAL_RETRY === $cmd->countRetry && 1 === $cmd->sendingRetry),
                self::callback(fn (array $stamps): bool => 1 === \count($stamps) && $stamps[0] instanceof DelayStamp && 600_000 === $stamps[0]->getDelay()),
            )
            ->willReturnCallback(fn (object $cmd): Envelope => new Envelope($cmd));

        $this->buildHandler($repository, $manager, $bus)(new VerifyCampaignDeliveryCommand(7, self::FINAL_RETRY, 0));
    }

    public function testInvokeWhenSentAfterSendingStartsConfirmWindow(): void
    {
        // The send just reached "sent" after a long sending phase (sendingRetry advanced, countRetry
        // still 0). The confirmation window opens now: countRetry increments, sendingRetry is preserved.
        $campaign = $this->buildCampaign(preparedCount: 93);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $manager = $this->mockReads($campaign, MailchimpStatusEnum::Sent, ['emails_sent' => 0]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(fn (VerifyCampaignDeliveryCommand $cmd): bool => 7 === $cmd->campaignId && 1 === $cmd->countRetry && 17 === $cmd->sendingRetry),
                self::callback(fn (array $stamps): bool => 1 === \count($stamps) && $stamps[0] instanceof DelayStamp && 180_000 === $stamps[0]->getDelay()),
            )
            ->willReturnCallback(fn (object $cmd): Envelope => new Envelope($cmd));

        $this->buildHandler($repository, $manager, $bus)(new VerifyCampaignDeliveryCommand(7, 0, 17));
    }

    public function testInvokeWithZeroDeliveryFinalAlerts(): void
    {
        // Terminal "sent" with a confirmed emails_sent=0 at the end of the confirmation window:
        // alert (logger->error → Sentry). No further reschedule.
        $campaign = $this->buildCampaign(preparedCount: 93);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $manager = $this->mockReads($campaign, MailchimpStatusEnum::Sent, ['emails_sent' => 0]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with('[Mailchimp][PostSendGuard] Zero delivery detected', self::callback(fn (array $ctx): bool => 7 === $ctx['campaign_id'] && 0 === $ctx['emails_sent']));

        $this->buildHandler($repository, $manager, $this->silentBus(), $logger)(new VerifyCampaignDeliveryCommand(7, self::FINAL_RETRY));
    }

    private function mockReads(MailchimpCampaign $campaign, MailchimpStatusEnum $status, array $report): Manager&\PHPUnit\Framework\MockObject\MockObject
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects(self::once())->method('getCampaignStatus')->with(self::identicalTo($campaign))->willReturn($status);
        $manager->expects(self::once())->method('getReportData')->with(self::identicalTo($campaign))->willReturn($report);

        return $manager;
    }

    private function silentBus(): MessageBusInterface
    {
        // Pure stub: dispatch is never invoked on the alert paths that use this helper.
        return $this->createStub(MessageBusInterface::class);
    }

    private function buildHandler(
        MailchimpCampaignRepository $repository,
        Manager $manager,
        MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ): VerifyCampaignDeliveryCommandHandler {
        return new VerifyCampaignDeliveryCommandHandler(
            $repository,
            $manager,
            new PostSendDeliveryGuard(), // real: pure logic, nothing to mock
            $bus,
            $logger ?? new NullLogger(),
        );
    }

    private function buildCampaign(?int $preparedCount, string $externalId = 'mc-abc'): MailchimpCampaign
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $campaign->setExternalId($externalId);
        $message->addMailchimpCampaign($campaign);

        $segment = new MailchimpStaticSegment($campaign);
        $segment->preparedCount = $preparedCount;
        $campaign->setMailchimpStaticSegment($segment);

        return $campaign;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $property = new \ReflectionObject($entity)->getProperty('id');
        $property->setValue($entity, $id);
    }
}
