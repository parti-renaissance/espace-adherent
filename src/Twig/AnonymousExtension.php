<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AnonymousExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('login_path_for_anonymous_follower', [AnonymousRuntime::class, 'generateLoginPathForAnonymousFollower']),
            new TwigFunction('register_path_for_anonymous_follower', [AnonymousRuntime::class, 'generateRegisterPathForAnonymousFollower']),
        ];
    }
}
