<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommitteeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            // Permissions
            new TwigFunction('is_host', [CommitteeRuntime::class, 'isHost']),
            new TwigFunction('can_follow', [CommitteeRuntime::class, 'canFollow']),
            new TwigFunction('can_unfollow', [CommitteeRuntime::class, 'canUnfollow']),
            new TwigFunction('can_see', [CommitteeRuntime::class, 'canSee']),
        ];
    }
}
