<?php

namespace Tests\AppBundle\Geocoder\Subscriber;

use AppBundle\Committee\CommitteeWasCreatedEvent;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\Committee;
use AppBundle\Geocoder\DummyGeocoder;
use AppBundle\Geocoder\GeocodableInterface;
use AppBundle\Geocoder\Subscriber\EntityAddressGeocodingSubscriber;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Membership\AdherentAccountWasActivatedEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class EntityAddressGeocodingSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private $manager;

    /* @var EntityAddressGeocodingSubscriber */
    private $subscriber;

    public function testOnAdherentAccountActivationCompletedSucceeds()
    {
        $adherent = $this->createAdherent('92 bld Victor Hugo');

        $this->assertInstanceOf(GeocodableInterface::class, $adherent);
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());

        $this->manager->expects($this->once())->method('flush');
        $this->subscriber->onAdherentAccountActivationCompleted(new AdherentAccountWasActivatedEvent(
            $adherent,
            AdherentActivationToken::generate($adherent)
        ));

        $this->assertSame(48.901058, $adherent->getLatitude());
        $this->assertSame(2.318325, $adherent->getLongitude());
    }

    public function testOnAdherentAccountActivationCompletedFails()
    {
        $adherent = $this->createAdherent('58 rue de Picsou');

        $this->assertInstanceOf(GeocodableInterface::class, $adherent);
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());

        $this->manager->expects($this->never())->method('flush');
        $this->subscriber->onAdherentAccountActivationCompleted(new AdherentAccountWasActivatedEvent(
            $adherent,
            AdherentActivationToken::generate($adherent)
        ));

        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());
    }

    public function testOnCommitteeCreatedSucceeds()
    {
        $committee = $this->createCommittee('6 rue Neyret');

        $this->assertInstanceOf(GeocodableInterface::class, $committee);
        $this->assertNull($committee->getLatitude());
        $this->assertNull($committee->getLongitude());

        $this->manager->expects($this->once())->method('flush');
        $this->subscriber->onCommitteeCreated(new CommitteeWasCreatedEvent($committee, $this->createAdherent('92 bld Victor Hugo')));

        $this->assertSame(45.7713288, $committee->getLatitude());
        $this->assertSame(4.8288758, $committee->getLongitude());
    }

    public function testOnCommitteeCreatedFailed()
    {
        $committee = $this->createCommittee('12 rue Jean Paul II');

        $this->assertInstanceOf(GeocodableInterface::class, $committee);
        $this->assertNull($committee->getLatitude());
        $this->assertNull($committee->getLongitude());

        $this->manager->expects($this->never())->method('flush');
        $this->subscriber->onCommitteeCreated(new CommitteeWasCreatedEvent($committee, $this->createAdherent('92 bld Victor Hugo')));

        $this->assertNull($committee->getLatitude());
        $this->assertNull($committee->getLongitude());
    }

    private function createCommittee(string $address): Committee
    {
        $committee = new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Lyon 1',
            'Le comité En Marche ! de Lyon village',
            'FR',
            '69001',
            '69001-69381',
            '69001-en-marche-lyon'
        );
        $committee->setLocation('69001', '69001-69381', $address);

        return $committee;
    }

    private function createAdherent(string $address): Adherent
    {
        return new Adherent(
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'john.smith@example.org',
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::STUDENT,
            'FR',
            $address,
            '92110-92024',
            '92110'
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $this->subscriber = new EntityAddressGeocodingSubscriber(new DummyGeocoder(), $this->manager);
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->subscriber = null;

        parent::tearDown();
    }
}
