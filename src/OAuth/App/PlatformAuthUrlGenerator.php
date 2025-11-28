<?php

declare(strict_types=1);

namespace App\OAuth\App;

use App\AppCodeEnum;
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

    public function generateLoginLink(): string
    {
        return $this->urlGenerator->generate('app_renaissance_login');
    }
}
