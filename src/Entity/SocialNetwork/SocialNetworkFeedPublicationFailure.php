<?php

declare(strict_types=1);

namespace App\Entity\SocialNetwork;

enum SocialNetworkFeedPublicationFailure: string
{
    case VideoNotTranscoded = 'video_not_transcoded';
    case PhotoNotCopied = 'photo_not_copied';
    case ImageNotCopied = 'image_not_copied';
    case AvatarNotCopied = 'avatar_not_copied';
}
