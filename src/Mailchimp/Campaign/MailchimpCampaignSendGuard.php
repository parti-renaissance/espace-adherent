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
 *  2. The remote `recipient_count` is bounded against the local `preparedCount` (the members we
 *     successfully pushed to the segment and Mailchimp acked), with two independent tolerances:
 *      - OVERSHOOT above `preparedCount * (1 + maxRecipientDriftPercent%)`: the segment is polluted
 *        (e.g. a previous wipe failed and stale members survived) → ABORT, to prevent a mass email
 *        to the wrong audience.
 *      - UNDERSHOOT below `preparedCount * (1 - maxRecipientUndershootPercent%)`: Mailchimp has not
 *        finished propagating the bulk member-add into the campaign audience yet (eventual
 *        consistency) → RETRY so the count can settle. The retry chain re-reads it on each attempt;
 *        once exhausted it sends to whatever is available — an undershoot only reaches fewer
 *        *legitimate* recipients, never a wrong audience, so a partial send is acceptable.
 *
 * An unreadable `recipient_count` (transient API error) also RETRIES, but is NOT force-sendable on
 * exhaustion: we never verified the audience, so blind-sending could reach a wrong one.
 *
 * Reference is `preparedCount`, NOT `expectedCount`: the latter includes emails refused/errored
 * at push time that legitimately will not receive the campaign.
 */
class MailchimpCampaignSendGuard
{
    public function __construct(
        private readonly Driver $driver,
        private readonly int $maxRecipientDriftPercent,
        private readonly int $maxRecipientUndershootPercent,
    ) {
        if ($maxRecipientDriftPercent < 0) {
            throw new \InvalidArgumentException(\sprintf('maxRecipientDriftPercent must be >= 0, got %d.', $maxRecipientDriftPercent));
        }

        // >= 100 would drive the undershoot floor to <= 0, disabling the readiness gate entirely.
        if ($maxRecipientUndershootPercent < 0 || $maxRecipientUndershootPercent >= 100) {
            throw new \InvalidArgumentException(\sprintf('maxRecipientUndershootPercent must be in [0, 100), got %d.', $maxRecipientUndershootPercent));
        }
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

        // 2. Reference count = members we successfully pushed and Mailchimp acknowledged (added).
        $prepared = $campaign->getMailchimpStaticSegment()?->preparedCount;

        if (null === $prepared || $prepared <= 0) {
            return SendDecision::abort('preparedCount missing or zero at send time.');
        }

        // 3. Real recipient count Mailchimp will actually send to.
        $recipientCount = $this->driver->getCampaignRecipientCount($externalId);

        if (null === $recipientCount) {
            // Couldn't read the safety counter (transient API/network blip). Retry, but do NOT
            // force-send on exhaustion: we never verified the audience size.
            return SendDecision::retry('recipient_count not readable from Mailchimp.');
        }

        $maxAllowed = (int) ceil($prepared * (1 + $this->maxRecipientDriftPercent / 100));
        $minAllowed = (int) floor($prepared * (1 - $this->maxRecipientUndershootPercent / 100));

        // Overshoot: more recipients than we pushed → polluted segment → never send.
        if ($recipientCount > $maxAllowed) {
            return SendDecision::abort(\sprintf('Recipient overshoot: recipient_count=%d prepared=%d max=%d', $recipientCount, $prepared, $maxAllowed), $recipientCount);
        }

        // Undershoot: fewer recipients than we pushed → segment still propagating on Mailchimp's
        // side → retry so the count can settle. Force-sendable on exhaustion: an undershoot only
        // reaches a subset of legitimate recipients.
        if ($recipientCount < $minAllowed) {
            return SendDecision::retry(\sprintf('Recipient undershoot: recipient_count=%d prepared=%d min=%d — segment likely still propagating.', $recipientCount, $prepared, $minAllowed), $recipientCount, true);
        }

        return SendDecision::send($recipientCount);
    }
}
