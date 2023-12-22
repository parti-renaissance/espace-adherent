<?php

namespace App\Renaissance\App;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentExpirableTokenInterface;
use App\OAuth\App\AbstractAppUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlGenerator extends AbstractAppUrlGenerator
{
    private string $appHost;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $renaissanceHost)
    {
        parent::__construct($urlGenerator);

        $this->appHost = $renaissanceHost;
    }

    public static function getAppCode(): string
    {
        return AppCodeEnum::RENAISSANCE;
    }

    public function generateHomepageLink(): string
    {
        return $this->urlGenerator->generate('renaissance_site', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function generateForLoginSuccess(Adherent $adherent): string
    {
        if (!$adherent->isRenaissanceUser()) {
            return $this->urlGenerator->generate('app_adhesion_index');
        }

        return $this->urlGenerator->generate('app_renaissance_adherent_space');
    }

    public function generateSuccessResetPasswordLink(Request $request): string
    {
        if ($request->query->has('is_creation')) {
            return $this->urlGenerator->generate('app_renaissance_adherent_creation_confirmation');
        }

        return static::generateHomepageLink();
    }

    public function generateCreatePasswordLink(
        Adherent $adherent,
        AdherentExpirableTokenInterface $token,
        array $urlParams = []
    ): string {
        return $this->urlGenerator->generate(
            'app_adherent_reset_password',
            array_merge($urlParams, [
                'app_domain' => $this->appHost,
                'adherent_uuid' => (string) $adherent->getUuid(),
                'reset_password_token' => (string) $token->getValue(),
            ]),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function generateLoginLink(): string
    {
        return $this->urlGenerator->generate('app_renaissance_login');
    }

    public function guessAppCodeFromRequest(Request $request): ?string
    {
        if ($request->attributes->get('app_domain', $request->getHost()) === $this->appHost) {
            return static::getAppCode();
        }

        return null;
    }

    public function generateLogout(): string
    {
        return $this->urlGenerator->generate('logout', ['app_domain' => $this->appHost], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
