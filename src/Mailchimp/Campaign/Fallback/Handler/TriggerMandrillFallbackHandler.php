<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MandrillFallbackChunk;
use App\Mailchimp\Campaign\Fallback\Message\SendMandrillFallbackChunkMessage;
use App\Mailchimp\Campaign\Fallback\Message\TriggerMandrillFallbackMessage;
use App\Mailchimp\Driver;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class TriggerMandrillFallbackHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly Manager $manager,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
        private readonly Driver $driver,
        private readonly int $cap,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(TriggerMandrillFallbackMessage $message): void
    {
        $campaign = $this->campaignRepository->find($message->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        if (!$this->campaignRepository->claimMandrillFallback($message->campaignId)) {
            return;
        }

        $forcedTestFallback = str_contains((string) $campaign->getMessage()->getSubject(), TriggerMandrillFallbackMessage::FORCE_FALLBACK_SUBJECT_TOKEN);

        // Checkpoint: Mailchimp may have delivered late between the zero-delivery verdict and now.
        $report = $this->manager->getReportData($campaign);
        if (!$forcedTestFallback && \array_key_exists('emails_sent', $report) && (int) $report['emails_sent'] > 0) {
            $campaign->markFallbackAborted();
            $this->entityManager->flush();

            $this->logger->warning('[Mandrill][Fallback] Mailchimp delivered before fallback — aborted', [
                'campaign_id' => $message->campaignId,
                'emails_sent' => (int) $report['emails_sent'],
            ]);

            return;
        }

        $segment = $campaign->getMailchimpStaticSegment();
        if (null === $segment || null === $segment->chunksTotal || $segment->chunksTotal < 1) {
            $campaign->markFallbackFailed();
            $this->entityManager->flush();

            $this->logger->error('[Mandrill][Fallback] No prepared segment/chunks — fallback failed', [
                'campaign_id' => $message->campaignId,
            ]);

            return;
        }

        $eligible = $this->memberRepository->countEligibleForMandrill($segment->id);

        if (0 === $eligible) {
            $campaign->markFallbackFailed();
            $this->entityManager->flush();

            $this->logger->error('[Mandrill][Fallback] No eligible recipient — fallback failed', [
                'campaign_id' => $message->campaignId,
            ]);

            return;
        }

        if ($eligible > $this->cap) {
            $campaign->markFallbackSkipped();
            $this->entityManager->flush();

            $this->logger->error('[Mandrill][Fallback] Recipient count above cap — manual validation required', [
                'campaign_id' => $message->campaignId,
                'eligible' => $eligible,
                'cap' => $this->cap,
            ]);

            return;
        }

        // Only fan out to chunks that still hold at least one eligible recipient: a chunk emptied
        // since preparation (all unsubscribed/cleaned/refused) would create a ledger row and a
        // message for a guaranteed no-op. Skip them.
        $eligibleChunks = $this->memberRepository->findChunkNumbersEligibleForMandrill($segment->id);

        // Fetch the campaign HTML rendered by Mailchimp (template decor included) once for the whole
        // fan-out and carry it on each chunk message, so the per-chunk handler stays free of any
        // Mailchimp I/O. The zero-delivery incident only breaks delivery, not the read API.
        $renderedHtml = $this->fetchRenderedHtml($campaign);
        if (null === $renderedHtml) {
            $campaign->markFallbackFailed();
            $this->entityManager->flush();

            $this->logger->error('[Mandrill][Fallback] Empty campaign content from Mailchimp — fallback failed', [
                'campaign_id' => $message->campaignId,
            ]);

            return;
        }

        // Create the per-chunk ledger rows and commit them BEFORE dispatching, so the chunk handler
        // (which claims Pending -> Sending) always finds its row — including the sync path in tests.
        foreach ($eligibleChunks as $chunkNumber) {
            $this->entityManager->persist(new MandrillFallbackChunk($campaign, $chunkNumber));
        }

        $campaign->markFallbackSent();
        $this->entityManager->flush();

        foreach ($eligibleChunks as $chunkNumber) {
            $this->bus->dispatch(new SendMandrillFallbackChunkMessage($message->campaignId, $chunkNumber, $renderedHtml));
        }

        $this->logger->warning('[Mandrill][Fallback] Fan-out dispatched', [
            'campaign_id' => $message->campaignId,
            'chunks' => \count($eligibleChunks),
            'eligible' => $eligible,
        ]);
    }

    private function fetchRenderedHtml(MailchimpCampaign $campaign): ?string
    {
        $externalId = $campaign->getExternalId();
        if (null === $externalId) {
            return null;
        }

        $html = $this->driver->getCampaignContent($externalId);

        return '' === $html ? null : $html;
    }
}
