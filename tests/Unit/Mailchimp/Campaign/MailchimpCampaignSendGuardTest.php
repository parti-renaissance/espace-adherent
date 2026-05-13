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
    private const int EXPECTED_COUNT = 93;
    private const int THRESHOLD_PERCENT = 5; // max overshoot = ceil(93 * 1.05) = 98

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

    public function testMissingExpectedCountAborts(): void
    {
        $campaign = $this->buildCampaign(expectedCount: null);

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
        self::assertStringContainsString('expectedCount', $decision->reason);
    }

    public function testNullRecipientCountRetries(): void
    {
        $campaign = $this->buildCampaign();

        $driver = $this->driverReturningRecipientCount(null);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Retry, $decision->kind);
        self::assertNull($decision->recipientCount);
    }

    public function testZeroRecipientCountSends(): void
    {
        // Mailchimp returning recipient_count=0 is NOT a guard concern: if MC is still computing
        // the audience, its own `actions/send` response will reject the send with "not ready" and
        // the existing retry chain handles it. The guard only stops the send on overshoot.
        $campaign = $this->buildCampaign();

        $driver = $this->driverReturningRecipientCount(0);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(0, $decision->recipientCount);
    }

    public function testSignificantUndershootSends(): void
    {
        // expectedCount=93, recipient=10. Massive undershoot, but the guard does NOT block here:
        // if MC is still computing it will reject the actual send → retry kicks in; if MC accepts,
        // that count is authoritative (MC says there are 10 recipients) and we proceed.
        $campaign = $this->buildCampaign();

        $driver = $this->driverReturningRecipientCount(10);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(10, $decision->recipientCount);
    }

    public function testOvershootAborts(): void
    {
        $campaign = $this->buildCampaign();
        // expectedCount=93, threshold=5% → max=98; 99 overshoots.
        $driver = $this->driverReturningRecipientCount(99);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Abort, $decision->kind);
        self::assertSame(99, $decision->recipientCount);
        self::assertStringContainsString('overshoot', $decision->reason);
    }

    public function testJustAtThresholdSends(): void
    {
        $campaign = $this->buildCampaign();
        // expectedCount=93, threshold=5% → ceil(93*1.05) = 98 inclusive.
        $driver = $this->driverReturningRecipientCount(98);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(98, $decision->recipientCount);
    }

    public function testExactMatchSends(): void
    {
        $campaign = $this->buildCampaign();
        $driver = $this->driverReturningRecipientCount(self::EXPECTED_COUNT);

        $decision = $this->buildGuard($driver)->evaluate($campaign);

        self::assertSame(SendDecisionEnum::Send, $decision->kind);
        self::assertSame(self::EXPECTED_COUNT, $decision->recipientCount);
    }

    private function buildGuard(Driver $driver): MailchimpCampaignSendGuard
    {
        return new MailchimpCampaignSendGuard($driver, self::THRESHOLD_PERCENT);
    }

    private function buildCampaign(
        ?string $externalId = self::EXTERNAL_ID,
        ?int $segmentId = self::SEGMENT_ID,
        ?int $expectedCount = self::EXPECTED_COUNT,
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
        $segment->expectedCount = $expectedCount;
        $campaign->setMailchimpStaticSegment($segment);

        return $campaign;
    }

    /**
     * Driver mock that goes through segment-id check OK and returns the given recipient count.
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
