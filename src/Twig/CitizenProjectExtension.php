<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CitizenProjectExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            // Permissions
            new TwigFunction('is_administrator', [CitizenProjectRuntime::class, 'isAdministrator']),
            new TwigFunction('is_promotable_administrator', [CitizenProjectRuntime::class, 'isPromotableAdministrator']),
            new TwigFunction('is_demotable_administrator', [CitizenProjectRuntime::class, 'isDemotableAdministrator']),
            new TwigFunction('can_follow_citizen_project', [CitizenProjectRuntime::class, 'canFollowCitizenProject']),
            new TwigFunction('can_unfollow_citizen_project', [CitizenProjectRuntime::class, 'canUnfollowCitizenProject']),
            new TwigFunction('can_see_citizen_project', [CitizenProjectRuntime::class, 'canSeeCitizenProject']),
            new TwigFunction('can_comment_citizen_project', [CitizenProjectRuntime::class, 'canCommentCitizenProject']),
            new TwigFunction('can_see_comment_citizen_project', [CitizenProjectRuntime::class, 'canSeeCommentCitizenProject']),
        ];
    }
}
