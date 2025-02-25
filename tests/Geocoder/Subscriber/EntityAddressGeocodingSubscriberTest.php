<?php

namespace Tests\App\Geocoder\Subscriber;

use App\Committee\Event\EditCommitteeEvent;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Geocoder\Geocoder;
use App\Geocoder\GeoPointInterface;
use App\Geocoder\Subscriber\EntityAddressGeocodingSubscriber;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Event\UserEvent;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Test\Geocoder\DummyGeocoder;

class EntityAddressGeocodingSubscriberTest extends AbstractKernelTestCase
{
    protected $manager;

    /* @var EntityAddressGeocodingSubscriber */
    private $subscriber;

    public function testOnAdherentAccountRegistrationCompletedSucceeds()
    {
        $adherent = $this->createNewAdherent('92 bld Victor Hugo');

        $this->assertInstanceOf(GeoPointInterface::class, $adherent);
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());

        $this->manager->expects($this->once())->method('flush');
        $this->subscriber->updateCoordinates(new UserEvent($adherent));

        $this->assertSame(48.901058, $adherent->getLatitude());
        $this->assertSame(2.318325, $adherent->getLongitude());
    }

    public function testOnAdherentAccountRegistrationCompletedFails()
    {
        $adherent = $this->createNewAdherent('58 rue de Picsou');

        $this->assertInstanceOf(GeoPointInterface::class, $adherent);
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());

        $this->subscriber->updateCoordinates(new UserEvent($adherent));

        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());
    }

    public function testOnAdherentProfileUpdatedWithSameAddressDoNothing()
    {
        $adherent = $this->createNewAdherent('92 bld Victor Hugo');

        $this->manager->expects($this->once())->method('flush');
        $this->subscriber->updateCoordinates(new UserEvent($adherent));

        $this->assertSame(48.901058, $adherent->getLatitude());
        $this->assertSame(2.318325, $adherent->getLongitude());

        $this->manager->expects($this->never())->method('flush');
        $this->subscriber->updateCoordinates(new UserEvent($adherent));

        $this->assertSame(48.901058, $adherent->getLatitude());
        $this->assertSame(2.318325, $adherent->getLongitude());
    }

    public function testOnAdherentProfileUpdatedWithNewAddressSucceeds()
    {
        $adherent = $this->createNewAdherent('92 bld Victor Hugo');

        $this->assertInstanceOf(GeoPointInterface::class, $adherent);
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());

        $this->manager->expects($this->once())->method('flush');
        $this->subscriber->updateCoordinates(new UserEvent($adherent));

        $this->assertSame(48.901058, $adherent->getLatitude());
        $this->assertSame(2.318325, $adherent->getLongitude());
    }

    public function testOnCommitteeCreatedSucceeds()
    {
        $committee = $this->createCommittee('6 rue Neyret');

        $this->assertInstanceOf(GeoPointInterface::class, $committee);
        $this->assertNull($committee->getLatitude());
        $this->assertNull($committee->getLongitude());

        $this->manager->expects($this->once())->method('flush');
        $this->subscriber->updateCoordinates(new EditCommitteeEvent($committee));

        $this->assertSame(45.7713288, $committee->getLatitude());
        $this->assertSame(4.8288758, $committee->getLongitude());
    }

    public function testOnCommitteeCreatedFailed()
    {
        $committee = $this->createCommittee('12 rue Jean Paul II');

        $this->assertInstanceOf(GeoPointInterface::class, $committee);
        $this->assertNull($committee->getLatitude());
        $this->assertNull($committee->getLongitude());

        $this->subscriber->updateCoordinates(new EditCommitteeEvent($committee));

        $this->assertNull($committee->getLatitude());
        $this->assertNull($committee->getLongitude());
    }

    private function createCommittee(string $address): Committee
    {
        return new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Lyon',
            'Le comité En Marche ! de Lyon village',
            $this->createPostAddress($address, '69001-69381'),
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69001-en-marche-clichy'
        );
    }

    private function createNewAdherent(string $address): Adherent
    {
        return Adherent::create(
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'ABC-234',
            'john.smith@example.org',
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress($address, '92110-92024')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $this->subscriber = new EntityAddressGeocodingSubscriber(new Geocoder(new DummyGeocoder()), $this->manager);
    }

    protected function tearDown(): void
    {
        $this->manager = null;
        $this->subscriber = null;

        parent::tearDown();
    }
}
