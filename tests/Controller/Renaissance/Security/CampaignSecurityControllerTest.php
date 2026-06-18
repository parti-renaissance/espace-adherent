<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Security;

use App\AppCodeEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Mailer\Message\Renaissance\RenaissanceMagicLinkMessage;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordMessage;
use App\OAuth\App\AuthAppUrlManager;
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

    #[DataProvider('getAdherentEmails')]
    public function testAlreadyAuthenticatedGuardsStayOnCampaignDomain(string $email): void
    {
        $this->authenticateAsAdherent($this->client, $email);

        // Guards "already authenticated -> app" must keep the user on the campaign host. A relative
        // "/app" location proves it: the redirect is built with app_domain = campaign host, so Symfony
        // emits a relative path. The pre-fix default vox host would have produced an absolute URL.
        foreach (['/connexion', '/mot-de-passe-oublie', '/demander-un-lien-magique'] as $path) {
            $this->client->request(Request::METHOD_GET, $path);
            $this->assertClientIsRedirectedTo('/app', $this->client);
        }
    }

    public function testMagicLinkRequestedOnCampaignHostTargetsCampaignDomain(): void
    {
        $email = 'carl999@example.fr';
        $campaignHost = static::getContainer()->getParameter('user_campaign_host');
        $voxHost = static::getContainer()->getParameter('user_vox_host');

        $crawler = $this->client->request(Request::METHOD_GET, '/demander-un-lien-magique');
        $this->client->submit($crawler->selectButton('Envoyez-moi un lien de connexion')->form(['email' => $email]));

        $messages = $this->getEmailRepository()->findRecipientMessages(RenaissanceMagicLinkMessage::class, $email);
        self::assertCount(1, $messages);

        // The generated magic link targets the campaign auth host (appCode derived from the request
        // host), not the vox host.
        $payload = json_decode($messages[0]->getRequestPayloadJson(), true);
        $vars = array_column($payload['message']['merge_vars'][0]['vars'], 'content', 'name');
        $magicLinkHost = parse_url($vars['magic_link'] ?? '', \PHP_URL_HOST);

        self::assertSame($campaignHost, $magicLinkHost, 'Magic link must target the campaign auth host.');
        self::assertNotSame($voxHost, $magicLinkHost);
    }

    public function testMagicLinkLoginRedirectsToWellFormedCampaignTarget(): void
    {
        $email = 'carl999@example.fr';
        $campaignHost = static::getContainer()->getParameter('user_campaign_host');
        $voxHost = static::getContainer()->getParameter('user_vox_host');

        // Request a magic link from the campaign host, then read the link back from the email.
        $crawler = $this->client->request(Request::METHOD_GET, '/demander-un-lien-magique');
        $this->client->submit($crawler->selectButton('Envoyez-moi un lien de connexion')->form(['email' => $email]));

        $messages = $this->getEmailRepository()->findRecipientMessages(RenaissanceMagicLinkMessage::class, $email);
        self::assertCount(1, $messages);

        $payload = json_decode($messages[0]->getRequestPayloadJson(), true);
        $vars = array_column($payload['message']['merge_vars'][0]['vars'], 'content', 'name');
        $parts = parse_url($vars['magic_link'] ?? '');
        self::assertSame($campaignHost, $parts['host']);

        // Consume the link (check_post_only => true). The post-login redirect must be a well-formed
        // absolute URL on the campaign host. Regression guard: when _target_path was baked as the
        // scheme-relative network path "//<vox_host>/app", HttpUtils::getUriForPath prepended the
        // current scheme+host and produced "http://<campaign>//<vox>/app" (a campaign user bounced to
        // the vox host through a malformed double-host URL).
        $this->client->request(Request::METHOD_POST, $parts['path'].'?'.$parts['query']);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $location = (string) $this->client->getResponse()->headers->get('location');

        self::assertSame('http://'.$campaignHost.'/app', $location);
        self::assertStringNotContainsString($voxHost, $location, 'Magic-link login must not bounce to the vox host.');
    }

    public function testCampaignLoginAccountCreationLinkTargetsCampaignSpa(): void
    {
        $campaignAppHost = static::getContainer()->getParameter('campaign_app_host');

        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // "Je crée un compte" must point to the campaign SPA (CAMPAIGN_APP_HOST) /bienvenue page,
        // where account creation happens in the app.
        $uri = $crawler->selectLink('Je crée un compte')->link()->getUri();
        $parts = parse_url($uri);
        $host = $parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        self::assertSame($campaignAppHost, $host, 'Account-creation link must target the campaign SPA host.');
        self::assertSame('/bienvenue', $parts['path']);
    }

    public function testForgotPasswordSubmitStaysOnCampaignDomain(): void
    {
        $voxHost = static::getContainer()->getParameter('user_vox_host');

        $crawler = $this->client->request(Request::METHOD_GET, '/mot-de-passe-oublie');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submitting the forgot-password form must redirect back to the same (campaign) page.
        // The success path redirects to the host-templated app_forgot_password route; without an
        // explicit app_domain it would default to the vox host and bounce the campaign user away.
        $this->client->submit($crawler->selectButton('Réinitialiser')->form(), ['form' => ['email' => 'carl999@example.fr']]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $location = (string) $this->client->getResponse()->headers->get('location');
        self::assertStringNotContainsString($voxHost, $location, 'Forgot-password submit must not bounce to the vox host.');
        self::assertStringEndsWith('/mot-de-passe-oublie', $location);
    }

    public function testForgotPasswordEmailLinkTargetsCampaignDomain(): void
    {
        $email = 'carl999@example.fr';
        $campaignHost = static::getContainer()->getParameter('user_campaign_host');
        $voxHost = static::getContainer()->getParameter('user_vox_host');

        $crawler = $this->client->request(Request::METHOD_GET, '/mot-de-passe-oublie');
        $this->client->submit($crawler->selectButton('Réinitialiser')->form(), ['form' => ['email' => $email]]);

        $messages = $this->getEmailRepository()->findRecipientMessages(RenaissanceResetPasswordMessage::class, $email);
        self::assertCount(1, $messages);

        // The reset link inside the email must point to the campaign auth host so the user lands
        // back on the domain they started from, not the vox host.
        $payload = json_decode($messages[0]->getRequestPayloadJson(), true);
        $vars = array_column($payload['message']['global_merge_vars'], 'content', 'name');
        $resetLinkHost = parse_url($vars['reset_link'] ?? '', \PHP_URL_HOST);

        self::assertSame($campaignHost, $resetLinkHost, 'Reset-password email link must target the campaign auth host.');
        self::assertNotSame($voxHost, $resetLinkHost);
    }

    public function testResetPasswordSuccessLinkTargetsCampaignDomain(): void
    {
        $campaignHost = static::getContainer()->getParameter('user_campaign_host');
        $voxHost = static::getContainer()->getParameter('user_vox_host');

        // Drive the campaign url generator with the router resolving on the campaign host: the
        // post-reset success link (consumed by SecurityController::resetPasswordAction) must stay
        // on the campaign domain rather than defaulting back to the vox login page.
        $router = static::getContainer()->get('router');
        $router->getContext()->setHost($campaignHost);

        $generator = static::getContainer()->get(AuthAppUrlManager::class)->getUrlGenerator(AppCodeEnum::CAMPAIGN);
        $link = $generator->generateSuccessResetPasswordLink(new Request());

        self::assertStringNotContainsString($voxHost, $link, 'Post-reset redirect must not bounce to the vox host.');
        self::assertStringEndsWith('/connexion', $link);
    }

    public function testMagicLinkRequestSubmitStaysOnCampaignDomain(): void
    {
        $voxHost = static::getContainer()->getParameter('user_vox_host');

        $crawler = $this->client->request(Request::METHOD_GET, '/demander-un-lien-magique');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Same host-stickiness expectation for the magic-link request success path.
        $this->client->submit($crawler->selectButton('Envoyez-moi un lien de connexion')->form(['email' => 'carl999@example.fr']));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $location = (string) $this->client->getResponse()->headers->get('location');
        self::assertStringNotContainsString($voxHost, $location, 'Magic-link request submit must not bounce to the vox host.');
        self::assertStringEndsWith('/demander-un-lien-magique', $location);
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
