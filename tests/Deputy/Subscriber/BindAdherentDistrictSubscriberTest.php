<?php

namespace Tests\App\Deputy\Subscriber;

use App\Deputy\Subscriber\BindAdherentDistrictSubscriber;
use App\Entity\Adherent;
use App\Entity\District;
use App\Entity\Geo\Zone;
use App\Entity\GeoData;
use App\Entity\ReferentTag;
use App\Entity\ReferentTaggableEntity;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Event\AdherentEvent;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('legacy')]
class BindAdherentDistrictSubscriberTest extends AbstractKernelTestCase
{
    protected $manager;

    private ?DistrictRepository $districtRepository;
    private ?BindAdherentDistrictSubscriber $subscriber;

    #[DataProvider('provideReferentTagHasCount')]
    public function testOnAdherentAccountRegistrationCompletedSucceeds(array $districts, int $count): void
    {
        $adherent = $this->createNewAdherent();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        if (0 < $count) {
            $this->manager->expects($this->once())->method('flush');
        }
        $this->districtRepository->expects($this->once())->method('findDistrictsByCoordinates')->willReturn($districts);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentEvent($adherent));

        $this->assertSame($count, $adherent->getReferentTags()->count());
    }

    #[DataProvider('provideReferentTagHasCount')]
    public function testOnAdherentProfileUpdatedSuccessfully(array $referentTags, int $count): void
    {
        $adherent = $this->createNewAdherent();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        if (0 < $count) {
            $this->manager->expects($this->once())->method('flush');
        }
        $this->districtRepository->expects($this->once())->method('findDistrictsByCoordinates')->willReturn($referentTags);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentEvent($adherent));

        $this->assertSame($count, $adherent->getReferentTags()->count());
    }

    private function createNewAdherent(): Adherent
    {
        return Adherent::create(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'john.smith@example.org',
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::EMPLOYED,
            $this->createPostAddress('26 rue de la Paix', '75008-75108', null, 48.869878, 2.332197)
        );
    }

    public static function provideReferentTagHasCount(): array
    {
        $tag1 = new ReferentTag('1ère circonscription, Paris', 'CIRCO_75001', new Zone('district', 'CIRCO_75001', '1ère circonscription, Paris'));
        $tag2 = new ReferentTag('Alpes-Maritimes, 1ère circonscription (06-01)', 'CIRCO_06001', new Zone('district', 'CIRCO_06001', 'Alpes-Maritimes, 1ère circonscription (06-01)'));
        $district1 = new District(
            ['FR'], 'Ain', '01001', 1, 1, new GeoData(new Polygon([])), $tag1
        );
        $district2 = new District(
            ['FR'], 'Ain', '01002', 2, 1, new GeoData(new Polygon([])), $tag2
        );
        $district3 = new District(
            ['GB', 'DK', 'EE', 'FI', 'IE', 'IS', 'LV', 'LT', 'NO', 'SE'], 'Français établis hors de France', 'FDE_03', 3, 999, new GeoData(new Polygon([])), $tag2
        );

        return [
            [[], 0],
            [[$district1], 1],
            [[$district2, $district1], 2],
            [[$district3, $district2, $district1], 2],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->districtRepository = $this->createMock(DistrictRepository::class);

        $this->subscriber = new BindAdherentDistrictSubscriber($this->manager, $this->districtRepository);
    }

    protected function tearDown(): void
    {
        $this->manager = null;
        $this->districtRepository = null;
        $this->subscriber = null;

        parent::tearDown();
    }
}
