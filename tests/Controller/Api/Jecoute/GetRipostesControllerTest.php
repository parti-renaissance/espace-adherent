<?php

namespace Tests\App\Controller\Api\Jecoute;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Jecoute\Riposte;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class GetRipostesControllerTest extends AbstractWebCaseTest
{
    use ApiControllerTestTrait;
    use ControllerTestTrait;

    private const URI = '/api/v3/ripostes';

    private $riposteRepository;

    public function testIncrementStatsWhileGettingRipostes(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'deputy@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, self::URI, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);

        $this->isSuccessful($this->client->getResponse());
        static::assertJson($this->client->getResponse()->getContent());
        $result = \GuzzleHttp\json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $result);

        $riposte1 = $this->riposteRepository->findOneBy(['uuid' => $result[0]['uuid']]);

        $this->assertSame(2, $riposte1->getNbViews());
        $this->assertSame(1, $riposte1->getNdDetailViews());
        $this->assertSame(1, $riposte1->getNbSourceViews());
        $this->assertSame(1, $riposte1->getNbRipostes());

        $riposte3 = $this->riposteRepository->findOneBy(['uuid' => $result[1]['uuid']]);

        $this->assertSame(2, $riposte3->getNbViews());
        $this->assertSame(0, $riposte3->getNdDetailViews());
        $this->assertSame(0, $riposte3->getNbSourceViews());
        $this->assertSame(1, $riposte3->getNbRipostes());
    }

    public function testNoIncrementStatsWhileGettingRipostes(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            null,
            'deputy@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, self::URI.'?scope=national', [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);

        $this->isSuccessful($this->client->getResponse());
        static::assertJson($this->client->getResponse()->getContent());
        $result = \GuzzleHttp\json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(4, $result);

        $riposte1 = $this->riposteRepository->findOneBy(['uuid' => $result[0]['uuid']]);

        $this->assertSame(1, $riposte1->getNbViews());
        $this->assertSame(1, $riposte1->getNdDetailViews());
        $this->assertSame(1, $riposte1->getNbSourceViews());
        $this->assertSame(1, $riposte1->getNbRipostes());

        $riposte3 = $this->riposteRepository->findOneBy(['uuid' => $result[2]['uuid']]);

        $this->assertSame(0, $riposte3->getNbViews());
        $this->assertSame(0, $riposte3->getNdDetailViews());
        $this->assertSame(0, $riposte3->getNbSourceViews());
        $this->assertSame(0, $riposte3->getNbRipostes());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->riposteRepository = $this->getRepository(Riposte::class);
    }

    protected function tearDown(): void
    {
        $this->riposteRepository = null;

        parent::tearDown();
    }
}
