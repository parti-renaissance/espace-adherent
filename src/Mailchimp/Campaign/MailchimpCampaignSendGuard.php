<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Driver;

/**
 * Last-line safety check before a Mailchimp campaign is actually sent.
 *
 * Two concerns:
 *  1. The remote campaign must still point to the saved segment we configured for it
 *     (`getCampaignSavedSegmentId` == local `staticSegmentId`).
 *  2. The remote `recipient_count` must be within an acceptable drift above the local
 *     `expectedCount`. Overshooting means the segment is polluted (e.g. a previous wipe failed
 *     and stale members survived) → block the send to prevent a mass email to the wrong audience.
 *
 * Undershoot is NOT handled here. Mailchimp itself rejects `actions/send` with a "campaign not
 * ready" error while it is still computing the audience; that error path already triggers the
 * existing retry/backoff via `Manager::sendMailchimpCampaign()` returning false. So we trust
 * Mailchimp's own readiness signal instead of trying to detect it from `recipient_count`.
 */
class MailchimpCampaignSendGuard
{
    public function __construct(
        private readonly Driver $driver,
        private readonly int $maxRecipientDriftPercent,
    ) {
    }

    public function evaluate(MailchimpCampaign $campaign): SendDecision
    {
        $externalId = $campaign->getExternalId();

        if (null === $externalId) {
            return SendDecision::abort('Missing external id at send time.');
        }

        // 1. Segment-id consistency between our DB and Mailchimp.
        $localSegmentId = $campaign->getStaticSegmentId();
        $remoteSegmentId = $this->driver->getCampaignSavedSegmentId($externalId);

        if ($remoteSegmentId !== $localSegmentId) {
            return SendDecision::abort(\sprintf('Segment mismatch: local=%s remote=%s', $localSegmentId ?? 'null', $remoteSegmentId ?? 'null'));
        }

        // 2. Reference count = the SQL audience size committed at preparation time.
        $expected = $campaign->getMailchimpStaticSegment()?->expectedCount;

        if (null === $expected || $expected <= 0) {
            return SendDecision::abort('expectedCount missing or zero at send time.');
        }

        // 3. Real recipient count Mailchimp will actually send to.
        $recipientCount = $this->driver->getCampaignRecipientCount($externalId);

        if (null === $recipientCount) {
            // Couldn't read the safety counter (transient API/network blip). Retry; if it stays
            // null over MAX_RETRIES, the existing exhaustion path force-sends with a Sentry error.
            return SendDecision::retry('recipient_count not readable from Mailchimp.');
        }

        $maxAllowed = (int) ceil($expected * (1 + $this->maxRecipientDriftPercent / 100));

        if ($recipientCount > $maxAllowed) {
            return SendDecision::abort(\sprintf('Recipient overshoot: recipient_count=%d expected=%d max=%d', $recipientCount, $expected, $maxAllowed), $recipientCount);
        }

        return SendDecision::send($recipientCount);
    }
}
