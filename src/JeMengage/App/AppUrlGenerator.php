<?php

namespace App\JeMengage\App;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentExpirableTokenInterface;
use App\OAuth\App\AbstractAppUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppUrlGenerator extends AbstractAppUrlGenerator
{
    private string $appHost;
    private string $appAuthHost;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $jemengageHost, string $jemengageAuthHost)
    {
        parent::__construct($urlGenerator);

        $this->appHost = $jemengageHost;
        $this->appAuthHost = $jemengageAuthHost;
    }

    public static function getAppCode(): string
    {
        return AppCodeEnum::JEMENGAGE;
    }

    public function guessAppCodeFromRequest(Request $request): ?string
    {
        if ($request->attributes->get('app_domain', $request->getHost()) === $this->appAuthHost) {
            return static::getAppCode();
        }

        return null;
    }

    public function generateHomepageLink(): string
    {
        return $this->appHost;
    }

    public function generateSuccessResetPasswordLink(Request $request): string
    {
        if ($request->query->has('is_creation')) {
            return $this->urlGenerator->generate('app_jemengage_creation_confirmation');
        }

        return '//'.$this->appAuthHost;
    }

    public function generateLoginLink(): string
    {
        return $this->urlGenerator->generate('app_jemengage_login');
    }

    public function generateCreatePasswordLink(
        Adherent $adherent,
        AdherentExpirableTokenInterface $token,
        array $urlParams = []
    ): string {
        return $this->urlGenerator->generate(
            'app_adherent_reset_password',
            array_merge($urlParams, [
                'app_domain' => $this->appAuthHost,
                'adherent_uuid' => (string) $adherent->getUuid(),
                'reset_password_token' => (string) $token->getValue(),
            ]),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function generateAdherentListLink(): string
    {
        return sprintf('//%s/adherents', $this->appAuthHost);
    }
}
