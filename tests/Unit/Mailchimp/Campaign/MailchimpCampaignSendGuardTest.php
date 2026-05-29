<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\MailchimpCampaignSendGuard;
use App\Mailchimp\Campaign\SendDecisionEnum;
use App\Mailchimp\Driver;
use PHPUnit\Framework\TestCase;

class MailchimpCampaignSendGuardTest extends TestCase
{
    private const string EXTERNAL_ID = 'mc-campaign-abc';
    private const int SEGMENT_ID = 555;
    private const int PREPARED_COUNT = 93;
    // Bounds around preparedCount=93 with the two tolerances below:
    //   overshoot block above ceil(93 * 1.05) = 98
    //   undershoot retry below floor(93 * 0.95) = 88
    private const int DRIFT_PERCENT = 5;
    private const int UNDERSHOOT_PERCENT = 5;

    public function testMissingExternalIdAborts(): void
    {
        $campaign = $this->buildCampaign(externalId: null);

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getCampaignSavedSegmentId');
        $driver->expects(self::never())->method('getCampaignRecipientCount');

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Abort, $decision->kind);
        self::assertStringContainsString('Missing external id', $decision->reason);
    }

    public function testSegmentMismatchAborts(): void
    {
        $campaign = $this->buildCampaign();

        $driver = $this->createMock(Driver::class);
        $driver
            ->expects(self::once())
            ->method('getCampaignSavedSegmentId')
            ->with(self::EXTERNAL_ID)
            ->willReturn(999) // remote points to a different segment than local 555
        ;
        $driver->expects(self::never())->method('getCampaignRecipientCount');

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Abort, $decision->kind);
        self::assertStringContainsString('Segment mismatch', $decision->reason);
    }

    public function testMissingPreparedCountAborts(): void
    {
        $campaign = $this->buildCampaign(preparedCount: null);

        $driver = $this->createMock(Driver::class);
        $driver
            ->expects(self::once())
            ->method('getCampaignSavedSegmentId')
            ->with(self::EXTERNAL_ID)
            ->willReturn(self::SEGMENT_ID)
        ;
        $driver->expects(self::never())->method('getCampaignRecipientCount');

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Abort, $decision->kind);
        self::assertStringContainsString('preparedCount', $decision->reason);
    }

    public function testNullRecipientCountRetries(): void
    {
        $campaign = $this->buildCampaign();

        $driver = $this->driverReturningRecipientCount(null);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Retry, $decision->kind);
        self::assertNull($decision->recipientCount);
    }

    public function testZeroRecipientCountRetries(): void
    {
        // Mailchimp reporting recipient_count=0 while we prepared 93 means the segment members
        // have not propagated yet on Mailchimp's side. Retry so the count can settle instead of
        // sending to nobody.
        $campaign = $this->buildCampaign();

        $driver = $this->driverReturningRecipientCount(0);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Retry, $decision->kind);
        self::assertSame(0, $decision->recipientCount);
    }

    public function testSignificantUndershootRetries(): void
    {
        // The production incident in miniature: preparedCount=93 but Mailchimp only reports 10
        // because it has not finished indexing the bulk member-add into the campaign audience.
        // The guard must NOT send the partial audience — it retries until the count settles.
        $campaign = $this->buildCampaign();

        $driver = $this->driverReturningRecipientCount(10);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Retry, $decision->kind);
        self::assertSame(10, $decision->recipientCount);
        self::assertStringContainsString('undershoot', $decision->reason);
    }

    public function testJustBelowUndershootFloorRetries(): void
    {
        $campaign = $this->buildCampaign();
        // floor(93 * 0.95) = 88 → 87 is just below the floor.
        $driver = $this->driverReturningRecipientCount(87);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Retry, $decision->kind);
        self::assertSame(87, $decision->recipientCount);
    }

    public function testUndershootRetryIsForceSendableOnExhaustion(): void
    {
        $campaign = $this->buildCampaign();
        $driver = $this->driverReturningRecipientCount(10);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Retry, $decision->kind);
        self::assertTrue($decision->forceSendOnExhaustion, 'A readable undershoot may be force-sent once retries are exhausted.');
    }

    public function testUnreadableCountRetryIsNotForceSendableOnExhaustion(): void
    {
        $campaign = $this->buildCampaign();
        $driver = $this->driverReturningRecipientCount(null);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Retry, $decision->kind);
        self::assertFalse($decision->forceSendOnExhaustion, 'An unreadable count must never be blind-sent on exhaustion.');
    }

    public function testAtUndershootFloorSends(): void
    {
        $campaign = $this->buildCampaign();
        // floor(93 * 0.95) = 88 inclusive.
        $driver = $this->driverReturningRecipientCount(88);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(88, $decision->recipientCount);
    }

    public function testMinorDecayWithinToleranceSends(): void
    {
        // Legitimate decay between push and send (a few unsubscribes/cleans) keeps the count just
        // under preparedCount. This is the fully-propagated happy path — send, don't retry.
        $campaign = $this->buildCampaign();

        $driver = $this->driverReturningRecipientCount(90);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(90, $decision->recipientCount);
    }

    public function testExactMatchSends(): void
    {
        $campaign = $this->buildCampaign();
        $driver = $this->driverReturningRecipientCount(self::PREPARED_COUNT);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(self::PREPARED_COUNT, $decision->recipientCount);
    }

    public function testAtOvershootThresholdSends(): void
    {
        $campaign = $this->buildCampaign();
        // ceil(93 * 1.05) = 98 inclusive.
        $driver = $this->driverReturningRecipientCount(98);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(98, $decision->recipientCount);
    }

    public function testOvershootAborts(): void
    {
        $campaign = $this->buildCampaign();
        // preparedCount=93, threshold=5% → max=98; 99 overshoots → segment is polluted, block.
        $driver = $this->driverReturningRecipientCount(99);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Abort, $decision->kind);
        self::assertSame(99, $decision->recipientCount);
        self::assertStringContainsString('overshoot', $decision->reason);
    }

    private function buildGuard(Driver $driver): MailchimpCampaignSendGuard
    {
        return new MailchimpCampaignSendGuard($driver, self::DRIFT_PERCENT, self::UNDERSHOOT_PERCENT);
    }

    private function buildCampaign(
        ?string $externalId = self::EXTERNAL_ID,
        ?int $segmentId = self::SEGMENT_ID,
        ?int $preparedCount = self::PREPARED_COUNT,
    ): MailchimpCampaign {
        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);

        if (null !== $externalId) {
            $campaign->setExternalId($externalId);
        }
        if (null !== $segmentId) {
            $campaign->setStaticSegmentId($segmentId);
        }

        $segment = new MailchimpStaticSegment($campaign);
        $segment->mailchimpSegmentId = $segmentId;
        $segment->expectedCount = self::PREPARED_COUNT;
        $segment->preparedCount = $preparedCount;
        $campaign->setMailchimpStaticSegment($segment);

        return $campaign;
    }

    /**
     * Driver mock that goes through the segment-id check OK and returns the given recipient count.
     */
    private function driverReturningRecipientCount(?int $recipientCount): Driver
    {
        $driver = $this->createMock(Driver::class);
        $driver
            ->expects(self::once())
            ->method('getCampaignSavedSegmentId')
            ->with(self::EXTERNAL_ID)
            ->willReturn(self::SEGMENT_ID)
        ;
        $driver
            ->expects(self::once())
            ->method('getCampaignRecipientCount')
            ->with(self::EXTERNAL_ID)
            ->willReturn($recipientCount)
        ;

        return $driver;
    }
}
