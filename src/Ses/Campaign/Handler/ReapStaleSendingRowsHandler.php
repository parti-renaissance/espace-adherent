<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Message\ReapStaleSendingRowsMessage;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Campaign\SesCampaignCompleter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class ReapStaleSendingRowsHandler
{
    /**
     * How long a row may legitimately stay Sending. The claim now happens after the rate-limiter wait, so the
     * window it covers is just the SES call itself (seconds): 15 minutes is far beyond any live send.
     */
    private const int STALE_AFTER_MINUTES = 15;
    /** Watchdog period. */
    private const int REARM_DELAY_MS = 5 * 60 * 1_000;
    /** ~24h of cycles. A send of the largest allowed audience takes minutes: reaching this means it is stuck. */
    private const int MAX_CYCLES = 288;

    private const string REASON = 'Worker died while sending — row abandoned in Sending, outcome unknown';

    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly SesCampaignCompleter $completer,
        private readonly SendErroredRowReconciler $reconciler,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(ReapStaleSendingRowsMessage $message): void
    {
        $campaign = $this->campaignRepository->find($message->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        // The campaign left Sending (completed, or failed): nothing left to watch.
        if (MailchimpStatusEnum::Sending !== $campaign->status) {
            return;
        }

        $segment = $campaign->getMailchimpStaticSegment();
        if (null === $segment) {
            return;
        }

        $claimedBefore = new \DateTimeImmutable(\sprintf('-%d minutes', self::STALE_AFTER_MINUTES));

        foreach ($this->memberRepository->findStaleSendingRowIds($segment->id, $claimedBefore) as $rowId) {
            // Guarded on the same staleness window: a row the worker closed in between is left alone.
            if (!$this->memberRepository->quarantineStaleSendingRow($rowId, $claimedBefore, self::REASON)) {
                continue;
            }

            $this->logger->error('[SES][Campaign] Abandoned row reaped — quarantined (SendErrored)', [
                'campaign_id' => $message->campaignId,
                'row' => $rowId,
            ]);

            $this->reconciler->arm($rowId);
        }

        // The reaped rows may have been the last thing blocking the completion — and the chunk that would
        // normally have closed the campaign is precisely the one that died.
        $this->completer->completeIfDone($campaign, $segment->id);

        $this->rearm($message, $segment->id);
    }

    private function rearm(ReapStaleSendingRowsMessage $message, int $staticSegmentId): void
    {
        if (0 === $this->memberRepository->countRemainingToSend($staticSegmentId)) {
            return;
        }

        if ($message->cycle >= self::MAX_CYCLES) {
            $this->logger->error('[SES][Campaign] Campaign still sending after the watchdog gave up — needs a look', [
                'campaign_id' => $message->campaignId,
                'cycles' => $message->cycle,
            ]);

            return;
        }

        $this->bus->dispatch(
            new ReapStaleSendingRowsMessage($message->campaignId, $message->cycle + 1),
            [new DelayStamp(self::REARM_DELAY_MS)],
        );
    }
}
