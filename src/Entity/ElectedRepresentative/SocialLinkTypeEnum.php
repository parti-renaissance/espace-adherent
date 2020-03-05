<?php

namespace AppBundle\Entity\ElectedRepresentative;

use MyCLabs\Enum\Enum;

final class SocialLinkTypeEnum extends Enum
{
    public const FACEBOOK = 'facebook';
    public const INSTAGRAM = 'instagram';
    public const TELEGRAM = 'linkedin';
    public const TWITTER = 'twitter';
    public const YOUTUBE = 'youtube';
}
