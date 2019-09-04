<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MunicipalSiteExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('municipal_site_is_enabled', [MunicipalSiteRuntime::class, 'isMunicipalSiteEnabled']),
        ];
    }
}
