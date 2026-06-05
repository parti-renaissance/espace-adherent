<?php

declare(strict_types=1);

namespace App\SocialNetwork\Publication\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Publication\Command\CheckSocialNetworkFeedPublicationCommand;
use App\SocialNetwork\Publication\SocialNetworkFeedReadinessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class CheckSocialNetworkFeedPublicationCommandHandler
{
    private const int POLL_DELAY_MS = 30000;

    // Must exceed the transcoding deadline (1h) so a video still being processed is not abandoned.
    private const int DEADLINE_SECONDS = 7200;

    private const array EXCLUDED_PLATFORMS = ['twitter'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SocialNetworkFeedRepository $repository,
        private readonly SocialNetworkFeedReadinessChecker $readinessChecker,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(CheckSocialNetworkFeedPublicationCommand $command): void
    {
        /** @var SocialNetworkFeed $feed */
        $feed = $this->repository->find($command->feedId);

        if (null === $feed) {
            return;
        }

        // Excluded platforms are never published, so the poller stops here without re-arming itself.
        if (\in_array($feed->platform, self::EXCLUDED_PLATFORMS, true)) {
            return;
        }

        if ($feed->score < 1) {
            return;
        }

        // Read committed state: another worker may have copied media since this message was queued.
        $this->entityManager->refresh($feed);

        if ($this->readinessChecker->isReadyToPublish($feed)) {
            // Already published and still ready: nothing to do.
            if (!$feed->published) {
                $feed->published = true;
                $feed->publishedAt = new \DateTimeImmutable();
                $feed->publicationFailure = null;
                $feed->publicationFailedAt = null;
                $this->entityManager->flush();
            }

            return;
        }

        // Not (or no longer) ready: a re-delivery may have introduced media that is not available yet.
        $pastDeadline = time() > $command->startedAt + self::DEADLINE_SECONDS;
        $dirty = false;

        if ($feed->published) {
            // Take a previously published post back down so it leaves the index until media is ready again.
            $feed->published = false;
            $feed->publishedAt = null;
            $dirty = true;
        }

        if ($pastDeadline) {
            $feed->publicationFailure = $this->readinessChecker->getBlockingReason($feed);
            $feed->publicationFailedAt = new \DateTimeImmutable();
            $dirty = true;
        }

        if ($dirty) {
            $this->entityManager->flush();
        }

        if (!$pastDeadline) {
            $this->bus->dispatch(
                new CheckSocialNetworkFeedPublicationCommand($command->feedId, $command->startedAt),
                [new DelayStamp(self::POLL_DELAY_MS)],
            );
        }
    }
}
