<?php

namespace Tests\App\Deputy\Subscriber;

use App\Deputy\Subscriber\BindAdherentZoneSubscriber;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Membership\ActivityPositions;
use App\Membership\AdherentAccountWasCreatedEvent;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BindAdherentZoneSubscriberTest extends TestCase
{
    private $manager;

    /* @var ZoneRepository */
    private $repository;

    /* @var BindAdherentZoneSubscriber */
    private $subscriber;

    /**
     * @dataProvider provideZones
     */
    public function testOnCompletedSucceeds(array $zones): void
    {
        $adherent = Adherent::create(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'john.smith@example.org',
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::EMPLOYED,
            PostAddress::createFrenchAddress('26 rue de la Paix', '75008-75108', null, 48.869878, 2.332197)
        );

        $this->assertSame(0, $adherent->getZones()->count());

        if (\count($zones) > 0) {
            $this->manager->expects($this->once())->method('flush');
        }

        $this->repository
            ->expects($this->once())
            ->method('findByCoordinatesAndTypes')
            ->with(
                48.869878,
                2.332197,
                [Zone::CANTON, Zone::DISTRICT, Zone::FOREIGN_DISTRICT],
            )
            ->willReturn($zones)
        ;

        $this->subscriber->updateZones(new AdherentAccountWasCreatedEvent($adherent));

        $this->assertSame(\count($zones), $adherent->getZones()->count());
    }

    public function provideZones(): array
    {
        $zone1 = new Zone('district', 'CIRCO_75001', '1ère circonscription, Paris');
        $zone2 = new Zone('district', 'CIRCO_06001', 'Alpes-Maritimes, 1ère circonscription (06-01)');

        return [
            [[]],
            [[$zone1]],
            [[$zone2, $zone1]],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(EntityManagerInterface::class);

        $this->repository = $this->createMock(ZoneRepository::class);
        $this->manager->expects($this->once())->method('getRepository')->willReturn($this->repository);

        $this->subscriber = new BindAdherentZoneSubscriber($this->manager);
    }

    protected function tearDown(): void
    {
        $this->manager = null;
        $this->repository = null;
        $this->subscriber = null;

        parent::tearDown();
    }
}
