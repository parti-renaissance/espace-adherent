<?php

namespace App\OAuth\App;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentExpirableTokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PlatformAuthUrlGenerator extends AbstractAppUrlGenerator
{
    public static function getAppCode(): string
    {
        return AppCodeEnum::PLATFORM;
    }

    public function generateHomepageLink(): string
    {
        return $this->urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function generateSuccessResetPasswordLink(Request $request): string
    {
        return $this->urlGenerator->generate('app_user_edit');
    }

    public function generateCreatePasswordLink(
        Adherent $adherent,
        AdherentExpirableTokenInterface $token,
        array $urlParams = []
    ): string {
        return $this->urlGenerator->generate(
            'app_adherent_reset_password',
            [
                'adherent_uuid' => (string) $adherent->getUuid(),
                'reset_password_token' => (string) $token->getValue(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function generateLoginLink(): string
    {
        return $this->urlGenerator->generate('app_user_login');
    }
}
