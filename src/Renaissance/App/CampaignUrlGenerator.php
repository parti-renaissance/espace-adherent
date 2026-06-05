<?php

declare(strict_types=1);

namespace App\Renaissance\App;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\OAuth\App\AbstractAppUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CampaignUrlGenerator extends AbstractAppUrlGenerator
{
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        private readonly string $userCampaignHost,
        private readonly string $campaignAppHost,
    ) {
        parent::__construct($urlGenerator);
    }

    public static function getAppCode(): string
    {
        return AppCodeEnum::CAMPAIGN;
    }

    public function generateHomepageLink(): string
    {
        return $this->urlGenerator->generate('campaign_site', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function generateForLoginSuccess(Adherent $adherent): string
    {
        return $this->urlGenerator->generate('vox_app_redirect');
    }

    public function generateSuccessResetPasswordLink(Request $request): string
    {
        if ($request->query->has('is_creation')) {
            return $this->urlGenerator->generate('app_renaissance_adherent_creation_confirmation');
        }

        return $this->generateLoginLink();
    }

    public function generateLoginLink(): string
    {
        return $this->urlGenerator->generate('app_renaissance_login');
    }

    public function generateLogout(): string
    {
        return $this->urlGenerator->generate('logout', ['app_domain' => $this->userCampaignHost], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getAppHost(): string
    {
        return $this->userCampaignHost;
    }

    public function getSpaHost(): string
    {
        return $this->campaignAppHost;
    }
}
