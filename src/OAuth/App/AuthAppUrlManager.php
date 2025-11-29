<?php

declare(strict_types=1);

namespace App\OAuth\App;

use App\AppCodeEnum;
use Symfony\Component\HttpFoundation\Request;

class AuthAppUrlManager
{
    /** @var iterable|AuthAppUrlGeneratorInterface[] */
    private iterable $urlGenerators;

    public function __construct(iterable $urlGenerators)
    {
        $this->urlGenerators = $urlGenerators instanceof \Traversable ? iterator_to_array($urlGenerators) : $urlGenerators;
    }

    public function getUrlGenerator(string $appCode): AuthAppUrlGeneratorInterface
    {
        return $this->urlGenerators[$appCode] ?? $this->urlGenerators[AppCodeEnum::RENAISSANCE];
    }

    public function getAppCodeFromRequest(Request $request): ?string
    {
        foreach ($this->urlGenerators as $urlGenerator) {
            if ($appCode = $urlGenerator->guessAppCodeFromRequest($request)) {
                return $appCode;
            }
        }

        return null;
    }
}
