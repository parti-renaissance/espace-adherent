<?php

namespace Tests\App\Deputy\Subscriber;

use App\Deputy\Subscriber\BindAdherentDistrictSubscriber;
use App\Entity\Adherent;
use App\Entity\District;
use App\Entity\GeoData;
use App\Entity\PostAddress;
use App\Entity\ReferentTag;
use App\Entity\ReferentTaggableEntity;
use App\Membership\ActivityPositions;
use App\Membership\AdherentAccountWasCreatedEvent;
use App\Membership\AdherentProfileWasUpdatedEvent;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BindAdherentDistrictSubscriberTest extends TestCase
{
    private $manager;

    /* @var DistrictRepository */
    private $districtRepository;

    /* @var BindAdherentDistrictSubscriber */
    private $subscriber;

    /**
     * @dataProvider provideReferentTagHasCount
     */
    public function testOnAdherentAccountRegistrationCompletedSucceeds(array $districts, int $count): void
    {
        $adherent = $this->createAdherent();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        if (0 < $count) {
            $this->manager->expects($this->once())->method('flush');
        }
        $this->districtRepository->expects($this->once())->method('findDistrictsByCoordinates')->willReturn($districts);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentAccountWasCreatedEvent($adherent));

        $this->assertSame($count, $adherent->getReferentTags()->count());
    }

    /**
     * @dataProvider provideReferentTagHasCount
     */
    public function testOnAdherentProfileUpdatedSuccessfully(array $referentTags, int $count): void
    {
        $adherent = $this->createAdherent();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        if (0 < $count) {
            $this->manager->expects($this->once())->method('flush');
        }
        $this->districtRepository->expects($this->once())->method('findDistrictsByCoordinates')->willReturn($referentTags);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentProfileWasUpdatedEvent($adherent));

        $this->assertSame($count, $adherent->getReferentTags()->count());
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
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
    }

    public function provideReferentTagHasCount(): array
    {
        /** @var GeoData $geoData */
        $geoData = $this->createMock(GeoData::class);

        $tag1 = new ReferentTag('1ère circonscription, Paris', 'CIRCO_75001');
        $tag2 = new ReferentTag('Alpes-Maritimes, 1ère circonscription (06-01)', 'CIRCO_06001');
        $district1 = new District(
            ['FR'], 'Ain', '01001', 1, 01, $geoData, $tag1
        );
        $district2 = new District(
            ['FR'], 'Ain', '01002', 2, 01, $geoData, $tag2
        );
        $district3 = new District(
            ['GB', 'DK', 'EE', 'FI', 'IE', 'IS', 'LV', 'LT', 'NO', 'SE'], 'Français établis hors de France', 'FDE_03', 3, 999, $geoData, $tag2
        );

        return [
            [[], 0],
            [[$district1], 1],
            [[$district2, $district1], 2],
            [[$district3, $district2, $district1], 2],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->districtRepository = $this->createMock(DistrictRepository::class);
        $this->manager->expects($this->once())->method('getRepository')->willReturn($this->districtRepository);
        $this->subscriber = new BindAdherentDistrictSubscriber($this->manager);
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->districtRepository = null;
        $this->subscriber = null;

        parent::tearDown();
    }
}
