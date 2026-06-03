<?php

declare(strict_types=1);

namespace App\SocialNetwork\Publication\Handler;

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

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SocialNetworkFeedRepository $repository,
        private readonly SocialNetworkFeedReadinessChecker $readinessChecker,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(CheckSocialNetworkFeedPublicationCommand $command): void
    {
        $feed = $this->repository->find($command->feedId);

        if (null === $feed) {
            return;
        }

        // Read committed state: another worker may have copied media since this message was queued.
        $this->entityManager->refresh($feed);

        if ($feed->published) {
            return;
        }

        if ($this->readinessChecker->isReadyToPublish($feed)) {
            $feed->published = true;
            $feed->publishedAt = new \DateTimeImmutable();
            $feed->publicationFailure = null;
            $feed->publicationFailedAt = null;
            $this->entityManager->flush();

            return;
        }

        if (time() > $command->startedAt + self::DEADLINE_SECONDS) {
            $feed->publicationFailure = $this->readinessChecker->getBlockingReason($feed);
            $feed->publicationFailedAt = new \DateTimeImmutable();
            $this->entityManager->flush();

            return;
        }

        $this->bus->dispatch(
            new CheckSocialNetworkFeedPublicationCommand($command->feedId, $command->startedAt),
            [new DelayStamp(self::POLL_DELAY_MS)],
        );
    }
}
