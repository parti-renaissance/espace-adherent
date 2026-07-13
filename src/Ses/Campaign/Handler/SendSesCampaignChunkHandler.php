<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Campaign\SesCampaignCompleter;
use App\Ses\Client\SesEmailClient;
use App\Ses\Rendering\SesMessageAssembler;
use App\Ses\Rendering\SesRecipient;
use App\Ses\Rendering\SesRecipientEmailFactory;
use AsyncAws\Core\Exception\Http\ClientException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\Exception\ExceptionInterface as LockExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\RateLimiter\Exception\MaxWaitDurationExceededException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Sends one chunk of a campaign through SES, one recipient at a time, with per-row at-most-once.
 *
 * Each row is claimed atomically (Added -> Sending) before its SES call: a redelivery or a concurrent
 * worker can only ever pick rows still Added, so no recipient is sent twice (red-team #2). A success
 * (markRowSent) and a permanent rejection (markRowRefused, with the SES reason) close the row terminally.
 * A transport failure is split by whether the send provably did not happen: a 429 throttle (provably
 * rejected) reopens the row (Sending -> Added) and re-dispatches, self-paced; an ambiguous failure
 * (5xx / network / empty MessageId — SES may already have sent) quarantines the row (markRowErrored,
 * Sending -> SendErrored, never re-claimed, so no double-send) and rethrows so Messenger retries the rest of
 * the chunk without resending the rows already done. The quarantine is not a dead end: an SES event later
 * proving the send promotes the row (SendErrored -> Sent), and a timed reconciliation alerts when none comes.
 *
 * The rate-limiter token is reserved BEFORE the row is claimed, never after: a reservation may sleep, and a
 * worker killed mid-sleep must not leave a claimed row behind — a row abandoned in Sending can never be
 * re-claimed and blocks the completion (that is the reaper's job to clean up, at a much higher cost than
 * simply keeping the row Added while we wait).
 *
 * When the chunk leaves no sendable row in the whole segment, the campaign is completed (see
 * SesCampaignCompleter) — idempotently, so two chunks finishing together do it once.
 */
#[AsMessageHandler]
class SendSesCampaignChunkHandler
{
    /** Max time a single token reservation may sleep before yielding the worker (re-dispatch instead). */
    private const float MAX_WAIT_SECONDS = 5.0;
    /** Max wall-clock a single invocation may hold the message before yielding the remaining chunk. */
    private const float MAX_INVOCATION_SECONDS = 12.0;
    /** Consecutive no-progress throttle re-dispatches at which a sustained stall escalates to an alert. */
    private const int THROTTLE_ALERT_THRESHOLD = 20;
    /** Backoff applied to a re-dispatched chunk. */
    private const int THROTTLE_BACKOFF_MS = 1_000;
    /** Single global bucket key: the send rate is an SES account-level cap shared by all workers. */
    private const string RATE_LIMITER_KEY = 'ses:global';

    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly SesMessageAssembler $assembler,
        private readonly SesRecipientEmailFactory $recipientEmailFactory,
        private readonly SesEmailClient $emailClient,
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly RateLimiterFactoryInterface $sesSendLimiter,
        private readonly SesCampaignCompleter $completer,
        private readonly SendErroredRowReconciler $reconciler,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SendSesCampaignChunkMessage $message): void
    {
        $campaign = $this->campaignRepository->find($message->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        $segment = $campaign->getMailchimpStaticSegment();
        if (null === $segment) {
            $this->logger->error('[SES][Campaign] Static segment missing — chunk skipped', [
                'campaign_id' => $message->campaignId,
                'chunk' => $message->chunkNumber,
            ]);

            return;
        }

        $recipients = $this->memberRepository->findClaimableRecipientsByChunk($segment->id, $message->chunkNumber);

        if ([] !== $recipients) {
            // Assemble the message-level HTML once per invocation; per-recipient codes are resolved below.
            $assembled = $this->assembler->assemble($campaign->getMessage());
            $limiter = $this->sesSendLimiter->create(self::RATE_LIMITER_KEY);
            $startedAt = microtime(true);
            $madeProgress = false;

            foreach ($recipients as $row) {
                // Backpressure: never hold the message past the invocation budget — re-dispatch the rest
                // (progress is preserved by per-row claims) so the worker is freed for other chunks.
                if ((microtime(true) - $startedAt) > self::MAX_INVOCATION_SECONDS) {
                    $this->redispatchRemaining($message, throttled: false, madeProgress: $madeProgress);

                    return;
                }

                $rowId = (int) $row['id'];

                // Pace to the global SES account rate (one token per email), BEFORE claiming the row: the wait
                // below can sleep for seconds, and the row must stay Added throughout — a worker killed while
                // sleeping on a claimed row would strand it in Sending for good.
                try {
                    $reservation = $limiter->reserve(1, self::MAX_WAIT_SECONDS);
                } catch (MaxWaitDurationExceededException) {
                    // Bucket saturated: nothing claimed, nothing sent — hand the rest back, self-paced (no loss).
                    $this->redispatchRemaining($message, throttled: true, madeProgress: $madeProgress);

                    return;
                } catch (LockExceptionInterface $exception) {
                    // Rate-limiter infra (Redis/lock) down → fail open: send without pacing, the 429 path stays
                    // the safety net. Never halt delivery on a limiter outage (cf. MailchimpSemaphore fail-open).
                    $this->logger->warning('[SES][Campaign] Rate limiter unavailable — sending without pacing', [
                        'campaign_id' => $message->campaignId,
                        'chunk' => $message->chunkNumber,
                        'exception' => $exception,
                    ]);
                    $reservation = null;
                }
                $reservation?->wait();

                // Claim: a row already taken (Sending/Sent) is skipped, never re-sent. Losing the race here
                // spends the token for nothing, which only paces us slightly below the cap — never above it.
                if (!$this->memberRepository->claimRowForSending($rowId)) {
                    continue;
                }

                $email = $this->recipientEmailFactory->create($assembled, new SesRecipient(
                    (string) $row['email'],
                    $row['uuid']->toRfc4122(),
                    (string) $row['firstName'],
                    (string) $row['lastName'],
                    $row['gender'],
                    $row['publicId'],
                    $rowId,
                ));

                try {
                    $outcome = $this->emailClient->sendEmail($email);
                } catch (ClientException $exception) {
                    // SesEmailClient only rethrows a ClientException on HTTP 429 (throttling): SES rejected the
                    // request, nothing was sent → reopen and re-dispatch, self-paced and unbounded (no loss).
                    $this->memberRepository->reopenRow($rowId);
                    $this->redispatchRemaining($message, throttled: true, madeProgress: $madeProgress);

                    return;
                } catch (\Throwable $exception) {
                    $this->memberRepository->markRowErrored($rowId, $exception->getMessage());
                    $this->logger->error('[SES][Campaign] Ambiguous send outcome — row quarantined (SendErrored)', [
                        'campaign_id' => $message->campaignId,
                        'chunk' => $message->chunkNumber,
                        'row' => $rowId,
                        'exception' => $exception,
                    ]);

                    $this->reconciler->arm($rowId);

                    throw $exception;
                }

                if (!$outcome->isSent()) {
                    $this->logger->warning('[SES][Campaign] Recipient permanently rejected', [
                        'campaign_id' => $message->campaignId,
                        'chunk' => $message->chunkNumber,
                        'row' => $rowId,
                        'reason' => $outcome->rejectionReason,
                    ]);

                    $this->suppressRecipientIfOnSuppressionList($outcome->rejectionReason, (string) $row['email']);
                    $this->memberRepository->markRowRefused($rowId, $outcome->rejectionReason);
                    $madeProgress = true;

                    continue;
                }

                $this->memberRepository->markRowSent($rowId);
                $madeProgress = true;
            }
        }

        $this->completer->completeIfDone($campaign, $segment->id);
    }

    private function redispatchRemaining(SendSesCampaignChunkMessage $message, bool $throttled, bool $madeProgress): void
    {
        $nextAttempt = ($madeProgress || !$throttled) ? 0 : $message->throttleAttempt + 1;

        if ($throttled && self::THROTTLE_ALERT_THRESHOLD === $nextAttempt) {
            $this->logger->error('[SES][Campaign] Send sustained throttling — chunk stuck, still retrying', [
                'campaign_id' => $message->campaignId,
                'chunk' => $message->chunkNumber,
                'throttle_attempt' => $nextAttempt,
            ]);
        } elseif ($throttled) {
            $this->logger->warning('[SES][Campaign] Send throttled — re-dispatching chunk', [
                'campaign_id' => $message->campaignId,
                'chunk' => $message->chunkNumber,
                'throttle_attempt' => $nextAttempt,
            ]);
        }

        $this->bus->dispatch(
            new SendSesCampaignChunkMessage($message->campaignId, $message->chunkNumber, $nextAttempt),
            [new DelayStamp(self::THROTTLE_BACKOFF_MS)],
        );
    }

    /**
     * A synchronous SES rejection because the address is on the account-level suppression list means the
     * mailbox is already known-dead: flag it locally so the next audience excludes it (this is the only
     * signal we get — a suppression-list address is never even attempted, so it produces no async SNS
     * bounce). Other permanent rejections (sender config, sandbox-unverified) are not the recipient's
     * fault, and an actually invalid mailbox surfaces as an async SNS hard bounce instead — both are left
     * as a warning only, to avoid wrongly suppressing live addresses on a systemic error.
     */
    private function suppressRecipientIfOnSuppressionList(?string $rejectionReason, string $email): void
    {
        if (null === $rejectionReason || !str_contains(strtolower($rejectionReason), 'suppression list')) {
            return;
        }

        $adherent = $this->adherentRepository->findOneByEmail($email);
        if (null === $adherent) {
            return;
        }

        $adherent->markAsEmailHardBounced();
        $this->entityManager->flush();
    }
}
