<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\PostAddress;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDefaultEventData extends Fixture implements DependentFixtureInterface
{
    private const EVENT_1_UUID = '5cab27a7-dbb3-4347-9781-566dad1b9eb5';
    private const EVENT_2_UUID = '2b7238f9-10ca-4a39-b8a4-ad7f438aa95f';
    private const EVENT_3_UUID = '4d962b05-68fe-4888-ab6b-53b96bdbe797';

    private $eventRegistrationFactory;

    public function __construct(EventRegistrationFactory $eventRegistrationFactory)
    {
        $this->eventRegistrationFactory = $eventRegistrationFactory;
    }

    public function load(ObjectManager $manager)
    {
        $referent = $this->getReference('adherent-8');

        $event1 = new DefaultEvent(Uuid::fromString(self::EVENT_1_UUID));
        $event1->setName('Nouvel événement online');
        $event1->setDescription('Description du nouvel événement online');
        $event1->setPublished(true);
        $event1->setBeginAt((new \DateTime('now'))->modify('+1 hour'));
        $event1->setFinishAt((new \DateTime('now'))->modify('+2 hours'));
        $event1->setCapacity(50);
        $event1->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event1->setMode(BaseEvent::MODE_ONLINE);
        $event1->setTimeZone('Europe/Paris');
        $event1->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));
        $event1->setPostAddress(PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
        $event1->setOrganizer($referent);

        $event2 = new DefaultEvent(Uuid::fromString(self::EVENT_2_UUID));
        $event2->setName('Nouvel événement online privé et électoral');
        $event2->setDescription('Description du nouvel événement online privé et électoral');
        $event2->setPublished(true);
        $event2->setBeginAt((new \DateTime('now'))->modify('+2 hours'));
        $event2->setFinishAt((new \DateTime('now'))->modify('+3 hours'));
        $event2->setCapacity(50);
        $event2->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event2->setMode(BaseEvent::MODE_ONLINE);
        $event2->setTimeZone('Europe/Paris');
        $event2->setOrganizer($referent);
        $event2->setPrivate(true);
        $event2->setElectoral(true);
        $event2->setPostAddress(PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
        $event2->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));

        $event3 = new DefaultEvent(Uuid::fromString(self::EVENT_3_UUID));
        $event3->setName('Un événement du référent annulé');
        $event3->setDescription('Description de l\'événement du référent annulé');
        $event3->setPublished(true);
        $event3->setBeginAt((new \DateTime('now'))->modify('-2 hours'));
        $event3->setFinishAt((new \DateTime('now'))->modify('-1 hour'));
        $event3->setCapacity(50);
        $event3->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event3->setMode(BaseEvent::MODE_ONLINE);
        $event3->setTimeZone('Europe/Paris');
        $event3->setOrganizer($referent);
        $event3->setPostAddress(PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
        $event3->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));
        $event3->cancel();

        $manager->persist($event1);
        $manager->persist($event2);
        $manager->persist($event3);

        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $this->getReference('adherent-7'))));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $this->getReference('user-1'))));
        $eventRegistration1 = new EventRegistrationCommand($event1);
        $eventRegistration1->setFirstName('Marie');
        $eventRegistration1->setLastName('CLAIRE');
        $eventRegistration1->setEmailAddress('marie.claire@test.com');
        $manager->persist($this->eventRegistrationFactory->createFromCommand($eventRegistration1));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $this->getReference('adherent-7'))));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadUserData::class,
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadReferentTagsZonesLinksData::class,
        ];
    }
}
