<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Vox;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class GetCurrentPronosticControllerTest extends AbstractApiTestCase
{
    use ApiControllerTestTrait;

    private const string PUBLIC_ENDPOINT = '/api/pronostics/current';
    private const string V3_ENDPOINT = '/api/v3/pronostics/current';
    private const string ADHERENT_EMAIL = 'jacques.picard@en-marche.fr';

    protected function setUp(): void
    {
        parent::setUp();

        // One stable kernel for the whole test (token request + inserts + GET share the EM/connection).
        $this->client->disableReboot();
        $this->manager->getConnection()->beginTransaction();
        $this->manager->getConnection()->executeStatement('DELETE FROM pronostic_participation');
        $this->manager->getConnection()->executeStatement('DELETE FROM pronostic');
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();

        parent::tearDown();
    }

    public function testReturnsNoContentWhenNoPronostic(): void
    {
        $this->client->request(Request::METHOD_GET, self::PUBLIC_ENDPOINT);

        self::assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        self::assertSame('', $this->client->getResponse()->getContent());
    }

    public function testIgnoresPronosticNotStartedYet(): void
    {
        $pronostic = $this->createStartedPronostic(matchAt: '+2 days');
        $pronostic->beginAt = new \DateTimeImmutable('+1 day');
        $this->manager->flush();

        $this->client->request(Request::METHOD_GET, self::PUBLIC_ENDPOINT);

        self::assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function testReturnsMostRecentStartedPronosticByMatchAt(): void
    {
        $this->createStartedPronostic('Vieux match', matchAt: '-2 hours');
        $this->createStartedPronostic('Dernier match', matchAt: '+2 days');
        $this->manager->flush();

        $this->client->request(Request::METHOD_GET, self::PUBLIC_ENDPOINT);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $payload = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('Dernier match', $payload['title']);
    }

    public function testReturnsPronosticEvenWhenNotDisplayedInAlert(): void
    {
        $alertPronostic = $this->createStartedPronostic('Affiché alerte', matchAt: '-1 hour');
        $alertPronostic->displayed = true;
        $this->createStartedPronostic('Dernier match', matchAt: '+2 days');
        $this->manager->flush();

        $this->client->request(Request::METHOD_GET, self::PUBLIC_ENDPOINT);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $payload = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('Dernier match', $payload['title']);
    }

    public function testAnonymousRequestReturnsCurrentPronosticWithoutParticipation(): void
    {
        $this->createStartedPronostic();
        $this->manager->flush();

        $this->client->request(Request::METHOD_GET, self::PUBLIC_ENDPOINT);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $payload = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame('France - Sénégal', $payload['title']);
        self::assertSame('France', $payload['team_1']);
        self::assertSame('Sénégal', $payload['team_2']);
        self::assertSame('not_participated', $payload['status']);
        self::assertNull($payload['participation']);
        self::assertArrayHasKey('begin_at', $payload);
        self::assertArrayHasKey('match_at', $payload);
        self::assertArrayHasKey('gabriel_pronostic', $payload);
        self::assertArrayHasKey('image_url', $payload);
    }

    public function testAuthenticatedRequestReturnsParticipation(): void
    {
        $pronostic = $this->createStartedPronostic();
        $adherent = $this->getAdherent();
        $this->manager->persist(new PronosticParticipation($pronostic, $adherent, 2, 1));
        $this->manager->flush();

        $token = $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            self::ADHERENT_EMAIL,
            LoadAdherentData::DEFAULT_PASSWORD,
        );

        $this->client->request(Request::METHOD_GET, self::V3_ENDPOINT, [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token",
        ]);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $payload = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame('participated', $payload['status']);
        self::assertSame(['team_1_score' => 2, 'team_2_score' => 1], $payload['participation']);
    }

    private function createStartedPronostic(string $title = 'France - Sénégal', string $matchAt = '+1 day'): Pronostic
    {
        $pronostic = new Pronostic();
        $pronostic->title = $title;
        $pronostic->team1 = 'France';
        $pronostic->team2 = 'Sénégal';
        $pronostic->gabrielTeam1Score = 1;
        $pronostic->gabrielTeam2Score = 0;
        $pronostic->beginAt = new \DateTimeImmutable('-1 day');
        $pronostic->matchAt = new \DateTimeImmutable($matchAt);

        $this->manager->persist($pronostic);

        return $pronostic;
    }

    private function getAdherent(): Adherent
    {
        return $this->manager->getRepository(Adherent::class)->findOneByEmail(self::ADHERENT_EMAIL);
    }
}
