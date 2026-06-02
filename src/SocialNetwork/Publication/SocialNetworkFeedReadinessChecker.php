<?php

declare(strict_types=1);

namespace App\SocialNetwork\Publication;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Repository\SocialNetworkFeedRepository;

class SocialNetworkFeedReadinessChecker
{
    public function __construct(private readonly SocialNetworkFeedRepository $repository)
    {
    }

    /**
     * A feed is ready to be published once every media it carries is available on our side:
     * the main image and the avatar are copied (when present), all photos are copied, and all
     * submitted videos are transcoded. A feed without media is ready immediately.
     */
    public function isReadyToPublish(SocialNetworkFeed $feed): bool
    {
        $mainImageReady = null === $feed->imageUrl || null !== $feed->publicImagePath;
        $avatarReady = null === $feed->avatarImageUrl || null !== $feed->publicAvatarImagePath;

        return $mainImageReady
            && $avatarReady
            && 0 === $this->repository->countUncopiedPhotos($feed)
            && 0 === $this->repository->countUntranscodedVideos($feed);
    }
}
