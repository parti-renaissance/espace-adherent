<?php

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
            new TwigFunction('is_supervisor', [CommitteeRuntime::class, 'isSupervisor']),
            new TwigFunction('is_promotable_host', [CommitteeRuntime::class, 'isPromotableHost']),
            new TwigFunction('is_demotable_host', [CommitteeRuntime::class, 'isDemotableHost']),
            new TwigFunction('can_follow', [CommitteeRuntime::class, 'canFollow']),
            new TwigFunction('can_unfollow', [CommitteeRuntime::class, 'canUnfollow']),
            new TwigFunction('can_create', [CommitteeRuntime::class, 'canCreate']),
            new TwigFunction('can_see', [CommitteeRuntime::class, 'canSee']),
            new TwigFunction('committee_color_status', [CommitteeRuntime::class, 'getCommitteeColorStatus']),
        ];
    }
}
