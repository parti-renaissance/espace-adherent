<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use PHPUnit\Framework\TestCase;

class MailchimpStaticSegmentTest extends TestCase
{
    public function testStartNewRunClearsRunStateAndIncrementsAttempts(): void
    {
        $segment = $this->buildSegmentHoldingPreviousRunState();

        $segment->startNewRun();

        self::assertSame(2, $segment->attempts, 'a new run bumps the attempt counter');
        self::assertNotNull($segment->buildStartedAt);

        // Everything describing the previous run must be gone. chunksTotal is the load-bearing one:
        // left set, it makes PrepareCampaignAudienceHandler short-circuit and skip the rebuild.
        self::assertNull($segment->chunksTotal);
        self::assertSame(0, $segment->chunksDone);
        self::assertNull($segment->builtAt);
        self::assertNull($segment->buildDurationMs);
        self::assertNull($segment->expectedCount);
        self::assertNull($segment->preparedCount);
        self::assertNull($segment->refusedCount);
        self::assertNull($segment->erroredCount);
        self::assertNull($segment->errorSummary);
    }

    public function testStartNewRunKeepsSegmentIdentityAndFilterSnapshot(): void
    {
        $segment = $this->buildSegmentHoldingPreviousRunState();

        $segment->startNewRun();

        // The segment is durable — it mirrors the remote Mailchimp segment — so its identity must
        // survive a new run. The filter snapshot is rewritten unconditionally by
        // captureFilterSnapshot() on every run; clearing it here would only blank the display.
        self::assertSame(110081, $segment->mailchimpSegmentId);
        self::assertSame('campaign_a5c2e061', $segment->name);
        self::assertSame(['zone' => 'Paris'], $segment->filterSnapshot);
        self::assertSame('08a560df', $segment->filterHash);
    }

    /**
     * Mirrors the state of the 2026-07-16 incident: a segment left behind by a failed Mailchimp
     * preparation (grain 500 → 7 chunks, 2 done, push errors recorded).
     */
    private function buildSegmentHoldingPreviousRunState(): MailchimpStaticSegment
    {
        $segment = new MailchimpStaticSegment(new MailchimpCampaign(new AdherentMessage()));

        $segment->mailchimpSegmentId = 110081;
        $segment->name = 'campaign_a5c2e061';
        $segment->filterSnapshot = ['zone' => 'Paris'];
        $segment->filterHash = '08a560df';

        $segment->attempts = 1;
        $segment->buildStartedAt = new \DateTimeImmutable('-3 hours');
        $segment->builtAt = new \DateTimeImmutable('-3 hours');
        $segment->buildDurationMs = 2903;
        $segment->expectedCount = 3284;
        $segment->preparedCount = 0;
        $segment->refusedCount = 0;
        $segment->erroredCount = 0;
        $segment->chunksTotal = 7;
        $segment->chunksDone = 2;
        $segment->errorSummary = 'HTTP 400 on chunk of 500 emails: None of the emails provided were subscribed to the list';

        return $segment;
    }
}
