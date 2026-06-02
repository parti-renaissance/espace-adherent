<?php

declare(strict_types=1);

namespace Tests\App\Security;

use App\Security\LoginThemeResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class LoginThemeResolverTest extends TestCase
{
    private const CAMPAIGN_HOST = 'campagne.renaissance.code';

    public function testResolveReturnsAttalThemeOnCampaignHost(): void
    {
        $resolver = new LoginThemeResolver(self::CAMPAIGN_HOST);

        $request = Request::create('https://'.self::CAMPAIGN_HOST.'/connexion');

        self::assertSame(LoginThemeResolver::THEME_ATTAL, $resolver->resolve($request));
    }

    public function testResolveReturnsRenaissanceThemeOnOtherHost(): void
    {
        $resolver = new LoginThemeResolver(self::CAMPAIGN_HOST);

        $request = Request::create('https://app.renaissance.code/connexion');

        self::assertSame(LoginThemeResolver::THEME_RENAISSANCE, $resolver->resolve($request));
    }

    public function testResolveUsesAppDomainAttributeOverHost(): void
    {
        $resolver = new LoginThemeResolver(self::CAMPAIGN_HOST);

        // The routing layer stores the matched host in the app_domain attribute,
        // which must take precedence over the raw Host header.
        $request = Request::create('https://app.renaissance.code/connexion');
        $request->attributes->set('app_domain', self::CAMPAIGN_HOST);

        self::assertSame(LoginThemeResolver::THEME_ATTAL, $resolver->resolve($request));
    }
}
