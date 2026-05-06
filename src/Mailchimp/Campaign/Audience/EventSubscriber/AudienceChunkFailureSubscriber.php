<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\EventSubscriber;

use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Repository\AdherentMessage\AdherentMessageTargetedRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Listens for ProcessAudienceChunkMessage failures that exhaust their Messenger retries
 * and marks the corresponding rows as errored, so the EXISTS pending check used by chunk
 * workers / finalize handler stops blocking on this dead chunk. Dispatches a finalize
 * message to rattraper the audience preparation completion.
 */
class AudienceChunkFailureSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly AdherentMessageTargetedRepository $targetedRepository,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry()) {
            return;
        }

        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof ProcessAudienceChunkMessage) {
            return;
        }

        // Truncate to keep DB rows lean even if upstream propagates large response bodies.
        $errorMessage = mb_substr($event->getThrowable()->getMessage(), 0, 2000);

        $this->logger->error('[AudienceChunk] Definitive failure, marking chunk as errored', [
            'campaign_id' => $message->mailchimpCampaignId,
            'chunk_number' => $message->chunkNumber,
            'error' => $errorMessage,
        ]);

        $this->targetedRepository->markChunkAsErroredByCampaignId(
            $message->mailchimpCampaignId,
            $message->chunkNumber,
            $errorMessage,
        );

        $this->bus->dispatch(new FinalizeCampaignAudienceMessage($message->mailchimpCampaignId));
    }
}
