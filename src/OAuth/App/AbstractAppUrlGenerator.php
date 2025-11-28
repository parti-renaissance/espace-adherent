<?php

declare(strict_types=1);

namespace App\OAuth\App;

use App\Entity\Adherent;
use App\Entity\AdherentExpirableTokenInterface;
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

    public function generateForLoginSuccess(Adherent $adherent): string
    {
        return $this->generateHomepageLink();
    }

    public function getAppHost(): string
    {
        return '';
    }

    public function generateCreatePasswordLink(
        Adherent $adherent,
        AdherentExpirableTokenInterface $token,
        array $urlParams = [],
    ): string {
        return $this->urlGenerator->generate(
            'app_adherent_reset_password',
            array_merge($urlParams, [
                'adherent_uuid' => (string) $adherent->getUuid(),
                'reset_password_token' => (string) $token->getValue(),
            ]),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
