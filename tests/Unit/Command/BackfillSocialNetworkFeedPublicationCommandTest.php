<?php

declare(strict_types=1);

namespace Tests\App\Unit\Command;

use App\Command\BackfillSocialNetworkFeedPublicationCommand;
use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\Entity\SocialNetwork\SocialNetworkFeedPublicationFailure;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedImagesCommand;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedPhotoCommand;
use App\SocialNetwork\Publication\Command\CheckSocialNetworkFeedPublicationCommand;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class BackfillSocialNetworkFeedPublicationCommandTest extends TestCase
{
    public function testResetsMediaPathsAndDispatchesReprocessing(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'https://cdn/img.jpg';
        $feed->publicImagePath = 'social-feed/old.jpg';
        $feed->publicationFailure = SocialNetworkFeedPublicationFailure::ImageNotCopied;
        $feed->publicationFailedAt = new \DateTimeImmutable('-1 hour');
        (function (): void { $this->id = 42; })->call($feed);

        $photo = new SocialNetworkFeedPhoto($feed);
        $photo->id = 7;
        $photo->src = 'https://cdn/photo.jpg';
        $photo->publicSrc = 'social-feed/oldphoto.jpg';
        $feed->addPhoto($photo);

        $repository = $this->createMock(SocialNetworkFeedRepository::class);
        $repository->expects(self::once())->method('findUnpublished')->with(null)->willReturn([$feed]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::exactly(3))->method('dispatch')->willReturnCallback(function (object $message) use (&$dispatched): Envelope {
            $dispatched[] = $message;

            return new Envelope($message);
        });

        $tester = new CommandTester(new BackfillSocialNetworkFeedPublicationCommand($repository, $entityManager, $bus));
        $tester->setInputs(['yes']);

        self::assertSame(0, $tester->execute([]));

        // Paths reset so the copy handlers re-publish to the media bucket.
        self::assertNull($feed->publicImagePath);
        self::assertNull($photo->publicSrc);
        // A fresh attempt window is armed, so any previous failure is cleared.
        self::assertNull($feed->publicationFailure);
        self::assertNull($feed->publicationFailedAt);

        $dispatchedTypes = array_map(static fn (object $message): string => $message::class, $dispatched);
        self::assertContains(PublishSocialNetworkFeedImagesCommand::class, $dispatchedTypes);
        self::assertContains(PublishSocialNetworkFeedPhotoCommand::class, $dispatchedTypes);
        self::assertContains(CheckSocialNetworkFeedPublicationCommand::class, $dispatchedTypes);
    }
}
