<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Security;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * The login page renders through the app-specific theme folder when it exists
 * (security/campaign/) and falls back to the default templates at the security/
 * root otherwise. This guards the fallback: a non-campaign host (app code
 * "renaissance") must render the default template — not the campaign override,
 * and not 500 on a missing folder.
 */
#[Group('functional')]
class SecurityThemeFallbackTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testLoginPageFallsBackToDefaultThemeOnVoxHost(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));

        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        // Default (security/ root) template...
        $this->assertStringContainsString('Je me connecte à <span', $crawler->html());
        // ...not the campaign override.
        $this->assertStringNotContainsString('Attal Président', $crawler->html());
    }
}
