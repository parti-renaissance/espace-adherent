<?php

namespace App\Renaissance\App;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppRenaissanceUrlGenerator extends UrlGenerator
{
    public function __construct(UrlGeneratorInterface $urlGenerator, string $appRenaissanceHost)
    {
        parent::__construct($urlGenerator, $appRenaissanceHost);
    }
}
