<?php

namespace Tests\App\Repository;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use App\Repository\PushTokenRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class PushTokenRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var ZoneRepository
     */
    private $zoneRepository;

    /**
     * @var PushTokenRepository
     */
    private $pushTokenRepository;

    /**
     * @dataProvider getTokensFromZone
     */
    public function testFindIdentifiersForZones(string $zoneType, string $zoneCode, array $expectedTokens): void
    {
        $zone = $this->zoneRepository->findOneBy([
            'type' => $zoneType,
            'code' => $zoneCode,
        ]);

        $tokens = $this->pushTokenRepository->findIdentifiersForZones([$zone]);

        self::assertSame($expectedTokens, $tokens);
    }

    public function getTokensFromZone(): iterable
    {
        yield [Zone::CITY, '77288', [
            'token-francis-jemarche-1',
            'token-francis-jemarche-2',
        ]];
        yield [Zone::DEPARTMENT, '92',  [
            'token-device-2-jemarche',
        ]];
        yield [Zone::REGION, '11', [
            'token-francis-jemarche-1',
            'token-francis-jemarche-2',
            'token-device-2-jemarche',
        ]];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->zoneRepository = $this->get(ZoneRepository::class);
        $this->pushTokenRepository = $this->get(PushTokenRepository::class);
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->zoneRepository = null;
        $this->pushTokenRepository = null;

        parent::tearDown();
    }
}
