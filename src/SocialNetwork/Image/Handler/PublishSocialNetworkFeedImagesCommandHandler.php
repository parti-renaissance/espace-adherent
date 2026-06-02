<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedImagesCommand;
use App\SocialNetwork\Image\PublishesFeedImageTrait;
use App\SocialNetwork\Image\Storage\FeedImagePublisherInterface;
use App\SocialNetwork\Image\Storage\PublishedImage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PublishSocialNetworkFeedImagesCommandHandler
{
    use PublishesFeedImageTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeedImagePublisherInterface $publisher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PublishSocialNetworkFeedImagesCommand $command): void
    {
        $feed = $this->entityManager->find(SocialNetworkFeed::class, $command->feedId);

        if (null === $feed) {
            return;
        }

        $this->publishSource($feed->imageUrl, $feed->publicImagePath, function (PublishedImage $published) use ($feed): void {
            $feed->publicImagePath = $published->path;
            $feed->imageWidth ??= $published->width;
            $feed->imageHeight ??= $published->height;
        });

        $this->publishSource($feed->avatarImageUrl, $feed->publicAvatarImagePath, function (PublishedImage $published) use ($feed): void {
            $feed->publicAvatarImagePath = $published->path;
            $feed->avatarWidth ??= $published->width;
            $feed->avatarHeight ??= $published->height;
        });

        $this->entityManager->flush();
    }
}
