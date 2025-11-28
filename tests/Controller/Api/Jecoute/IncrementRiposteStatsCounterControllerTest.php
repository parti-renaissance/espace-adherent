<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Jecoute;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadJecouteRiposteData;
use App\Entity\Jecoute\Riposte;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class IncrementRiposteStatsCounterControllerTest extends AbstractApiTestCase
{
    use ApiControllerTestTrait;
    use ControllerTestTrait;

    private const URI = '/api/v3/ripostes/%s/action/%s?scope=national';

    private $riposteRepository;

    #[DataProvider('provideRiposteActions')]
    public function testIncrementRiposteStatsCounterSuccessfully(string $riposteUuid, string $action): void
    {
        $riposte = $this->riposteRepository->findOneBy(['uuid' => $riposteUuid]);

        $this->assertRiposteStats($riposte, $action, 0);

        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'deputy@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_PUT, \sprintf(self::URI, $riposteUuid, $action), [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);

        $this->isSuccessful($this->client->getResponse());
        self::assertEquals('"OK"', $this->client->getResponse()->getContent());

        $this->manager->clear();
        $riposte = $this->riposteRepository->findOneBy(['uuid' => $riposteUuid]);

        $this->assertRiposteStats($riposte, $action, 1);
    }

    public static function provideRiposteActions(): \Generator
    {
        yield [LoadJecouteRiposteData::RIPOSTE_3_UUID, Riposte::ACTION_DETAIL_VIEW];
        yield [LoadJecouteRiposteData::RIPOSTE_3_UUID, Riposte::ACTION_SOURCE_VIEW];
        yield [LoadJecouteRiposteData::RIPOSTE_3_UUID, Riposte::ACTION_RIPOSTE];
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

    private function assertRiposteStats(Riposte $riposte, string $action, int $count): void
    {
        switch ($action) {
            case Riposte::ACTION_DETAIL_VIEW:
                $this->assertSame($count, $riposte->getNbDetailViews());

                break;
            case Riposte::ACTION_SOURCE_VIEW:
                $this->assertSame($count, $riposte->getNbSourceViews());

                break;
            case Riposte::ACTION_RIPOSTE:
                $this->assertSame($count, $riposte->getNbRipostes());

                break;
        }
    }
}
