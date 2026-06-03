<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Decides what to do when a transcoding command hits TranscoderAtCapacityException: either re-dispatch
 * it with a delay and an incremented attempt counter (so the work resumes once a quota slot frees), or —
 * once the per-chain attempt cap is reached — give up and mark the video FAILED so it stays recoverable
 * via `app:video:retranscode --status=failed` instead of orphaning in PENDING. Shared by the ingest and
 * relaunch handlers (same logic, different command type — the message rebuilds its own next attempt).
 */
class TranscoderCapacityDeferral
{
    public const int MAX_ATTEMPTS = 20;
    public const int DEFERRAL_DELAY_MS = 60000;
    // = MAX_ATTEMPTS * DEFERRAL_DELAY_MS / 60000. Upper bound (minutes) of one deferral chain's lifetime:
    // the PENDING-orphan recovery (retranscode --status=pending) must look only past this horizon so it
    // never re-dispatches a video whose chain is still alive (which would race a second createJob).
    public const int DEFERRAL_HORIZON_MINUTES = 20;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function deferOrFail(
        CapacityAwareMessageInterface $message,
        Video $video,
        TranscoderAtCapacityException $exception,
    ): void {
        $attempt = $message->getCapacityAttempt();
        $context = [
            'uuid' => $video->getUuid()->toRfc4122(),
            'cause' => $exception->cause,
            'active_jobs' => $exception->activeJobCount,
            'message' => $message::class,
        ];

        if ($attempt >= self::MAX_ATTEMPTS) {
            $video->status = VideoStatusEnum::FAILED;
            $video->failureReason = \sprintf('Transcoder at capacity, gave up after %d attempts.', $attempt);
            // Only stamp a finish time for a job that actually started; a deferred PENDING video never did.
            if (null !== $video->transcodingStartedAt) {
                $video->transcodingFinishedAt = new \DateTimeImmutable();
            }
            $this->entityManager->flush();

            $this->logger->error('[Video transcode] gave up: transcoder at capacity.', $context + ['attempt' => $attempt, 'max_attempts' => self::MAX_ATTEMPTS]);

            return;
        }

        // No flush here: the video keeps its current status (PENDING for ingest, its prior terminal status
        // for relaunch) with updatedAt frozen, so the matching `retranscode --status=...` recovery can find
        // it if this re-dispatch is ever lost. DelayStamp is a *minimum* delay (the cap, not the clock, is
        // the hard bound): under sync routing it caps inline recursion at MAX_ATTEMPTS; in production a
        // backlog can push delivery past the nominal horizon, which the orphan recovery accounts for.
        $this->bus->dispatch($message->withNextCapacityAttempt(), [new DelayStamp(self::DEFERRAL_DELAY_MS)]);

        $this->logger->warning('[Video transcode] deferred: transcoder at capacity.', $context + ['attempt' => $attempt + 1, 'max_attempts' => self::MAX_ATTEMPTS]);
    }
}
