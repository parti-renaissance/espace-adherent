<?php

declare(strict_types=1);

namespace Tests\App\Renaissance\App;

use App\AppCodeEnum;
use App\Renaissance\App\CampaignUrlGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CampaignUrlGeneratorTest extends TestCase
{
    private const CAMPAIGN_HOST = 'campagne.renaissance.code';
    private const CAMPAIGN_APP_HOST = 'campaign-app.code';

    public function testGetAppCode(): void
    {
        self::assertSame(AppCodeEnum::CAMPAIGN, CampaignUrlGenerator::getAppCode());
    }

    public function testGetAppHostReturnsCampaignHost(): void
    {
        $generator = new CampaignUrlGenerator($this->createStub(UrlGeneratorInterface::class), self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        self::assertSame(self::CAMPAIGN_HOST, $generator->getAppHost());
    }

    public function testGetSpaHostReturnsCampaignAppHost(): void
    {
        $generator = new CampaignUrlGenerator($this->createStub(UrlGeneratorInterface::class), self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        self::assertSame(self::CAMPAIGN_APP_HOST, $generator->getSpaHost());
    }

    public function testGuessAppCodeFromRequestMatchesCampaignHost(): void
    {
        $generator = new CampaignUrlGenerator($this->createStub(UrlGeneratorInterface::class), self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        $request = Request::create('https://app.renaissance.code/app');
        $request->attributes->set('app_domain', self::CAMPAIGN_HOST);

        self::assertSame(AppCodeEnum::CAMPAIGN, $generator->guessAppCodeFromRequest($request));
    }

    public function testGuessAppCodeFromRequestReturnsNullOnOtherHost(): void
    {
        $generator = new CampaignUrlGenerator($this->createStub(UrlGeneratorInterface::class), self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        $request = Request::create('https://app.renaissance.code/app');

        self::assertNull($generator->guessAppCodeFromRequest($request));
    }

    public function testGenerateLoginLink(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('app_renaissance_login')
            ->willReturn('/connexion');

        $generator = new CampaignUrlGenerator($urlGenerator, self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        self::assertSame('/connexion', $generator->generateLoginLink());
    }

    public function testGenerateHomepageLinkTargetsCampaignSite(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('campaign_site', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://campagne.code/');

        $generator = new CampaignUrlGenerator($urlGenerator, self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        self::assertSame('https://campagne.code/', $generator->generateHomepageLink());
    }

    public function testGenerateLogoutPassesCampaignHostAsAppDomain(): void
    {
        // Regression guard: generateLogout() used to reference an undefined
        // $this->appHost property, which resolved to null and broke the route.
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('logout', ['app_domain' => self::CAMPAIGN_HOST], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://'.self::CAMPAIGN_HOST.'/deconnexion');

        $generator = new CampaignUrlGenerator($urlGenerator, self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        self::assertSame('https://'.self::CAMPAIGN_HOST.'/deconnexion', $generator->generateLogout());
    }

    public function testGenerateSuccessResetPasswordLinkReturnsLoginLinkByDefault(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('app_renaissance_login')
            ->willReturn('/connexion');

        $generator = new CampaignUrlGenerator($urlGenerator, self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        self::assertSame('/connexion', $generator->generateSuccessResetPasswordLink(Request::create('/changer-mot-de-passe/x/y')));
    }

    public function testGenerateSuccessResetPasswordLinkTargetsCreationConfirmationOnCreation(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('app_renaissance_adherent_creation_confirmation')
            ->willReturn('/compte-cree');

        $generator = new CampaignUrlGenerator($urlGenerator, self::CAMPAIGN_HOST, self::CAMPAIGN_APP_HOST);

        self::assertSame('/compte-cree', $generator->generateSuccessResetPasswordLink(Request::create('/changer-mot-de-passe/x/y?is_creation=1')));
    }
}
