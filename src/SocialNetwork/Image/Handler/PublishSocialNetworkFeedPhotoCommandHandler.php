<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedPhotoCommand;
use App\SocialNetwork\Image\PublishesFeedImageTrait;
use App\SocialNetwork\Image\Storage\FeedImagePublisherInterface;
use App\SocialNetwork\Image\Storage\PublishedImage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PublishSocialNetworkFeedPhotoCommandHandler
{
    use PublishesFeedImageTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeedImagePublisherInterface $publisher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PublishSocialNetworkFeedPhotoCommand $command): void
    {
        $photo = $this->entityManager->find(SocialNetworkFeedPhoto::class, $command->photoId);

        if (null === $photo) {
            return;
        }

        $this->publishSource($photo->src, $photo->publicSrc, function (PublishedImage $published) use ($photo): void {
            $photo->publicSrc = $published->path;
            $photo->width ??= $published->width;
            $photo->height ??= $published->height;
        });

        $this->entityManager->flush();
    }
}
