<?php

declare(strict_types=1);

namespace App\Legislative\App;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\OAuth\App\AbstractAppUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlGenerator extends AbstractAppUrlGenerator
{
    private string $appHost;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $userVoxHost)
    {
        parent::__construct($urlGenerator);

        $this->appHost = $userVoxHost;
    }

    public static function getAppCode(): string
    {
        return AppCodeEnum::LEGISLATIVE;
    }

    public function generateHomepageLink(): string
    {
        return $this->urlGenerator->generate('legislative_site', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function generateForLoginSuccess(Adherent $adherent): string
    {
        return $this->urlGenerator->generate('vox_app_redirect');
    }

    public function generateSuccessResetPasswordLink(Request $request): string
    {
        return $this->generateLoginLink();
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

    public function getAppHost(): string
    {
        return $this->appHost;
    }
}
