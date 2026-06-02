<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Security;

use App\DataFixtures\ORM\LoadAdherentData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * Validates that the user login page is served on the campaign host
 * (user_campaign_host) and that the login -> /app -> /oauth/v2/auth
 * redirect chain stays on the campaign domain.
 */
#[Group('functional')]
#[Group('security')]
class CampaignSecurityControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private const EXPECTED_AUTHORIZE_URL = '/oauth/v2/auth?response_type=code&client_id=8128979a-cfdb-45d1-a386-f14f22bb19ae&redirect_uri=http://localhost:8081&scope=jemarche_app%20read:profile%20write:profile';

    public function testLoginPageIsServedWithAttalThemeOnCampaignHost(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Attal theme markers must be present...
        $this->assertStringContainsString('Attal Président', $crawler->html());
        $this->assertCount(1, $crawler->selectButton('Je me connecte'));

        // ...and the Renaissance theme heading must be absent.
        $this->assertStringNotContainsString('Je me connecte à <span', $crawler->html());
    }

    #[DataProvider('getAdherentEmails')]
    public function testAuthenticationStaysOnCampaignDomain(string $email): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_username' => $email,
            '_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        // 1. Login success redirects to /app as an absolute URL on the SAME
        //    (campaign) host. assertClientIsRedirectedTo(..., true) asserts the
        //    location equals "http://<current host>/app", i.e. the campaign host.
        $this->assertClientIsRedirectedTo('/app', $this->client, true);

        // 2. /app (RedirectAppController) initiates the OAuth authorization flow.
        //    The location is a relative path: RedirectAppController generates it
        //    with app_domain = campaign host, which matches the current host, so
        //    Symfony emits a relative URL — proving the flow stays on the campaign
        //    host (a vox-host target would have produced an absolute URL).
        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo(self::EXPECTED_AUTHORIZE_URL, $this->client);
    }

    public function testUnauthenticatedAuthorizeRequestRedirectsToCampaignLogin(): void
    {
        $campaignHost = static::getContainer()->getParameter('user_campaign_host');

        // Hitting the OAuth authorize endpoint unauthenticated must bounce back
        // to the login page on the campaign host (not the vox host).
        $this->client->request(Request::METHOD_GET, self::EXPECTED_AUTHORIZE_URL);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertSame(
            'http://'.$campaignHost.'/connexion',
            $this->client->getResponse()->headers->get('location'),
            'Unauthenticated authorize request must redirect to the campaign login page.'
        );
    }

    /**
     * The user_renaissance_redirect route ("/") redirects to the login page on
     * the SAME host it was reached on (the host is carried through app_domain via
     * RedirectController's _route_params), without a dedicated route per domain.
     *
     * Asserted on every user-facing host. The vox host is skipped when the test
     * environment collapses it onto RENAISSANCE_HOST (both "test.renaissance.code"
     * by default): "/" on that host is then captured by the controller-less
     * renaissance_site route, so the redirect cannot be exercised. Give USER_VOX_HOST
     * a subdomain distinct from RENAISSANCE_HOST in .env.test to cover it too.
     */
    #[DataProvider('provideUserHosts')]
    public function testRootRedirectsToLoginOnSameDomain(string $hostParameter): void
    {
        $host = static::getContainer()->getParameter($hostParameter);

        if ($host === static::getContainer()->getParameter('renaissance_host')) {
            self::markTestSkipped(\sprintf('Host "%s" is aliased to renaissance_host; "/" is shadowed by the controller-less renaissance_site route. Set a distinct USER_VOX_HOST in .env.test to enable this case.', $host));
        }

        $this->client->setServerParameter('HTTP_HOST', $host);
        $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertSame(
            'http://'.$host.'/connexion',
            $this->client->getResponse()->headers->get('location'),
            'Root must redirect to the login page on the same host.'
        );
    }

    public static function provideUserHosts(): array
    {
        return [
            'vox host' => ['user_vox_host'],
            'campaign host' => ['user_campaign_host'],
        ];
    }

    public static function getAdherentEmails(): array
    {
        return [
            ['renaissance-user-1@en-marche-dev.fr'],
            ['carl999@example.fr'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_campaign_host'));
    }
}
