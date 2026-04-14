<?php

declare(strict_types=1);

namespace Tests\App\Controller\OAuth;

use App\DataFixtures\ORM\LoadClientData;
use App\Entity\OAuth\Client;
use App\Repository\AdherentRepository;
use App\Repository\OAuth\ClientRepository;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Builder;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class OidcEndSessionControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testEndSessionWithValidHintAndWhitelistedUriRedirectsToTarget(): void
    {
        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('user_vox_host'));

        $client = $this->getDashboardRfeClient();
        $idToken = $this->buildIdTokenForCarl($client);

        $this->client->request(Request::METHOD_GET, '/oauth/v2/end-session', [
            'id_token_hint' => $idToken,
            'post_logout_redirect_uri' => 'http://localhost:8080',
            'state' => 'opaque-state-abc',
        ]);
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame(
            'http://localhost:8080?state=opaque-state-abc',
            $response->headers->get('Location'),
        );
    }

    public function testEndSessionWithValidHintAndUnwhitelistedUriRedirectsToLogin(): void
    {
        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('user_vox_host'));

        $idToken = $this->buildIdTokenForCarl($this->getDashboardRfeClient());

        $this->client->request(Request::METHOD_GET, '/oauth/v2/end-session', [
            'id_token_hint' => $idToken,
            'post_logout_redirect_uri' => 'https://attacker.example.com/steal',
        ]);
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame('/connexion', $response->headers->get('Location'));
    }

    public function testEndSessionWithValidHintAndNoUriRedirectsToLogin(): void
    {
        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('user_vox_host'));

        $idToken = $this->buildIdTokenForCarl($this->getDashboardRfeClient());

        $this->client->request(Request::METHOD_GET, '/oauth/v2/end-session', [
            'id_token_hint' => $idToken,
        ]);
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame('/connexion', $response->headers->get('Location'));
    }

    private function getDashboardRfeClient(): Client
    {
        $client = $this->getContainer()->get(ClientRepository::class)->findOneByUuid(LoadClientData::CLIENT_15_UUID);
        self::assertNotNull($client, 'Dashboard RFE fixture (client15) must be loaded');

        return $client;
    }

    private function buildIdTokenForCarl(Client $client): string
    {
        $adherent = $this->getContainer()->get(AdherentRepository::class)->findOneByEmail('carl999@example.fr');
        self::assertNotNull($adherent, 'Carl fixture must be loaded');

        $privateKey = InMemory::file($this->getParameter('ssl_private_key'));

        return new Builder(new JoseEncoder(), ChainedFormatter::withUnixTimestampDates())
            ->issuedBy($this->getParameter('oidc.issuer'))
            ->permittedFor($client->getUuid()->toString())
            ->relatedTo($adherent->getUuidAsString())
            ->issuedAt(new \DateTimeImmutable('now'))
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken(new Sha256(), $privateKey)
            ->toString();
    }
}
