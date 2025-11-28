<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AnonymousExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('login_path_for_anonymous_follower', [AnonymousRuntime::class, 'generateLoginPathForAnonymousFollower']),
            new TwigFunction('adhesion_path_for_anonymous_follower', [AnonymousRuntime::class, 'generateAdhesionPathForAnonymousFollower']),
        ];
    }
}
