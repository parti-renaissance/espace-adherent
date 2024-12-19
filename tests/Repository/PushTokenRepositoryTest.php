<?php

namespace Tests\App\Repository;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use App\Repository\PushTokenRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class PushTokenRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var ZoneRepository
     */
    private $zoneRepository;

    /**
     * @var PushTokenRepository
     */
    private $pushTokenRepository;

    #[DataProvider('getTokensFromZone')]
    public function testFindIdentifiersForZones(string $zoneType, string $zoneCode, array $expectedTokens): void
    {
        $zone = $this->zoneRepository->findOneBy([
            'type' => $zoneType,
            'code' => $zoneCode,
        ]);

        $tokens = $this->pushTokenRepository->findAllForZone($zone);

        self::assertSame($expectedTokens, $tokens);
    }

    public static function getTokensFromZone(): iterable
    {
        yield [Zone::CITY, '77288', [
            'token-francis-jemarche-1',
            'token-francis-jemarche-2',
        ]];
        yield [Zone::DEPARTMENT, '77',  [
            'token-francis-jemarche-1',
            'token-francis-jemarche-2',
        ]];
        yield [Zone::REGION, '11', [
            'token-francis-jemarche-1',
            'token-francis-jemarche-2',
        ]];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->zoneRepository = $this->get(ZoneRepository::class);
        $this->pushTokenRepository = $this->get(PushTokenRepository::class);
    }

    protected function tearDown(): void
    {
        $this->zoneRepository = null;
        $this->pushTokenRepository = null;

        parent::tearDown();
    }
}
