<?php

declare(strict_types=1);

namespace App\SocialNetwork\Publication;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPublicationFailure;
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
     *
     * Kept deliberately cheap (scalar checks short-circuit before the DB COUNTs): this runs on every
     * poll tick (~30s). The richer, priority-ordered classification lives in getBlockingReason() and is
     * only paid once, at the deadline.
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

    /**
     * The single most significant requirement still blocking publication, by priority
     * (video > photo > image > avatar), or null when the feed is ready. Only called at the deadline to
     * record why a feed was abandoned, so it can afford to be exhaustive — unlike isReadyToPublish().
     */
    public function getBlockingReason(SocialNetworkFeed $feed): ?SocialNetworkFeedPublicationFailure
    {
        if ($this->repository->countUntranscodedVideos($feed) > 0) {
            return SocialNetworkFeedPublicationFailure::VideoNotTranscoded;
        }

        if ($this->repository->countUncopiedPhotos($feed) > 0) {
            return SocialNetworkFeedPublicationFailure::PhotoNotCopied;
        }

        if (null !== $feed->imageUrl && null === $feed->publicImagePath) {
            return SocialNetworkFeedPublicationFailure::ImageNotCopied;
        }

        if (null !== $feed->avatarImageUrl && null === $feed->publicAvatarImagePath) {
            return SocialNetworkFeedPublicationFailure::AvatarNotCopied;
        }

        return null;
    }
}
