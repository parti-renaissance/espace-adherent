<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;

/**
 * Post-send delivery verification.
 *
 * After a campaign is sent, Mailchimp can report status=sent while delivering to 0 recipients
 * (emails_sent=0) — a silent failure the pre-send recipient_count guard cannot catch (the GET
 * returns a correct live count, actions/send freezes the audience to 0). The only reliable signal
 * is post-send: emails_sent from the report.
 *
 * Pure decision logic (no I/O): the handler performs the reads and acts on the verdict.
 *
 * Key safety rule: a campaign still in `sending` is NEVER a failure. Mailchimp may still be
 * delivering; replicating/re-sending it would risk a double send once the in-flight send completes.
 * Only a TERMINAL `sent` with a confirmed `emails_sent === 0` (and an expected audience) is a KO.
 * An unreadable report (emails_sent === null) is treated as not-yet-ready, never as a confirmed zero.
 *
 * Two independent windows, never conflated: the `sending` wait (a large national send can stay
 * "sending" for hours) and the post-`sent` confirmation wait (report propagation, ~30 min). The
 * confirmation window only opens once the campaign reaches "sent" — see {@see $confirmWindowExhausted}.
 */
class PostSendDeliveryGuard
{
    public function evaluate(
        MailchimpStatusEnum $status,
        ?int $emailsSent,
        ?int $preparedCount,
        bool $sendingWindowExhausted,
        bool $confirmWindowExhausted,
    ): DeliveryDecision {
        // At least one email left → healthy, regardless of everything else (catches a national that
        // is progressing, even while still "sending").
        if (null !== $emailsSent && $emailsSent > 0) {
            return DeliveryDecision::ok($emailsSent);
        }

        // Still actively sending: never treat as failure/fallback (double-send guard). Keep polling
        // on the dedicated sending window — the confirmation window MUST NOT start until "sent".
        if (MailchimpStatusEnum::Sending === $status) {
            return $sendingWindowExhausted
                ? DeliveryDecision::stillSending('Still "sending" past the max send window — Mailchimp may still be delivering.')
                : DeliveryDecision::pending('status=sending, awaiting completion.');
        }

        // Terminal "sent" is the only state where a confirmed zero is a real failure. The
        // confirmation window starts here (first time the campaign is observed as "sent").
        if (MailchimpStatusEnum::Sent === $status) {
            // A send happened but the expected audience is unknown → never silently OK.
            if (null === $preparedCount || $preparedCount <= 0) {
                return $confirmWindowExhausted
                    ? DeliveryDecision::unverifiable('status=sent but preparedCount unknown — cannot confirm delivery.')
                    : DeliveryDecision::pending('status=sent, preparedCount unknown, awaiting report.');
            }

            // Report not readable yet (404/transient). Retry; never fallback on an unreadable report.
            if (null === $emailsSent) {
                return $confirmWindowExhausted
                    ? DeliveryDecision::unverifiable('status=sent but report unreadable at end of window.')
                    : DeliveryDecision::pending('status=sent, report not ready yet.');
            }

            // emails_sent === 0, confirmed, prepared > 0 → zero delivery.
            return $confirmWindowExhausted
                ? DeliveryDecision::failed('status=sent but emails_sent=0 — zero delivery confirmed.', $emailsSent)
                : DeliveryDecision::pending('status=sent, emails_sent=0, awaiting report propagation.');
        }

        // Any other status (save/schedule/error/canceled/…): the campaign never reached a sending/sent
        // state. Bounded by the (short) confirmation window — long-polling a send that never started
        // or errored is pointless.
        if (null === $preparedCount || $preparedCount <= 0) {
            return $confirmWindowExhausted
                ? DeliveryDecision::unverifiable('Campaign not sending and preparedCount unknown.')
                : DeliveryDecision::pending('Campaign not yet sending.');
        }

        return $confirmWindowExhausted
            ? DeliveryDecision::notSending(\sprintf('Campaign stuck in status "%s" with prepared=%d — never started sending.', $status->value, $preparedCount))
            : DeliveryDecision::pending('Campaign not yet sending.');
    }
}
