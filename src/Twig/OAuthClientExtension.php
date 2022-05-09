<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OAuthClientExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_jme_client_id', [OAuthClientRuntime::class, 'getJMEClientId']),
        ];
    }
}
