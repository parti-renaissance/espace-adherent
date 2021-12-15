<?php

namespace App\OAuth\App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractAppUrlGenerator implements AuthAppUrlGeneratorInterface
{
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function guessAppCodeFromRequest(Request $request): ?string
    {
        return null;
    }
}
