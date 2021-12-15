<?php

namespace App\OAuth\App;

use App\Entity\Adherent;
use App\Entity\AdherentExpirableTokenInterface;
use App\Membership\MembershipSourceEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PlatformAuthUrlGenerator extends AbstractAppUrlGenerator
{
    public static function getAppCode(): string
    {
        return MembershipSourceEnum::PLATFORM;
    }

    public function generateHomepageLink(): string
    {
        return $this->urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function generateCreatePasswordLink(Adherent $adherent, AdherentExpirableTokenInterface $token): string
    {
        return $this->urlGenerator->generate(
            'adherent_reset_password',
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
