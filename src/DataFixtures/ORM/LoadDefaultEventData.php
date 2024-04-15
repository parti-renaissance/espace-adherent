<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\DefaultEvent;
use App\Event\EventRegistrationCommand;
use App\Event\EventVisibilityEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDefaultEventData extends AbstractLoadEventData implements DependentFixtureInterface
{
    public const EVENT_1_UUID = '5cab27a7-dbb3-4347-9781-566dad1b9eb5';
    private const EVENT_2_UUID = '2b7238f9-10ca-4a39-b8a4-ad7f438aa95f';
    private const EVENT_3_UUID = '4d962b05-68fe-4888-ab6b-53b96bdbe797';
    private const EVENT_4_UUID = '594e7ad0-c289-49ae-8c23-0129275d128b';
    private const EVENT_5_UUID = 'f4c66254-f6d3-4c28-bcb1-6e254d0d329c';
    private const EVENT_6_UUID = 'e770cda4-b215-4ea2-85e5-03fc3e4423e3';
    private const EVENT_7_UUID = '06d88cb2-d254-4ba3-9e00-b9d4611d90fc';

    public function loadEvents(ObjectManager $manager): void
    {
        $referent = $this->getReference('adherent-8');
        $senatorialCandidate = $this->getReference('senatorial-candidate');
        $adherent5 = $this->getReference('adherent-5');
        $adherentRe4 = $this->getReference('renaissance-user-4');
        $pad92 = $this->getReference('renaissance-user-4');

        $eventCategory7 = $this->getReference('CE007');

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
        $event1->setPostAddress($this->createPostAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
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
        $event2->setPostAddress($this->createPostAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
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
        $event3->setPostAddress($this->createPostAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
        $event3->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));
        $event3->cancel();

        $event4 = new DefaultEvent(Uuid::fromString(self::EVENT_4_UUID));
        $event4->setName('Un événement du candidat aux législatives');
        $event4->setDescription('Description de l\'événement du candidat aux législatives');
        $event4->setPublished(true);
        $event4->setBeginAt((new \DateTime('now'))->modify('+2 hours'));
        $event4->setFinishAt((new \DateTime('now'))->modify('+4 hours'));
        $event4->setCapacity(50);
        $event4->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event4->setMode(BaseEvent::MODE_MEETING);
        $event4->setTimeZone('Europe/Paris');
        $event4->setOrganizer($senatorialCandidate);
        $event4->setPostAddress($this->createPostAddress('74 Avenue des Champs-Élysées, 75008 Paris', '75008-75108', null, 48.862725, 2.287592));
        $event4->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event4->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));

        $event5 = new DefaultEvent(Uuid::fromString(self::EVENT_5_UUID));
        $event5->setName('Un événement de l\'assemblée départementale annulé');
        $event5->setDescription('Description de l\'événement de l\'assemblée départementale annulé');
        $event5->setCategory($eventCategory7);
        $event5->setPublished(true);
        $event5->setBeginAt((new \DateTime('now'))->modify('-3 hours'));
        $event5->setFinishAt((new \DateTime('now'))->modify('-2 hour'));
        $event5->setCapacity(50);
        $event5->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event5->setMode(BaseEvent::MODE_ONLINE);
        $event5->setTimeZone('Europe/Paris');
        $event5->setOrganizer($adherent5);
        $event5->setPostAddress($this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684));
        $event5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $event5->incrementParticipantsCount();
        $event5->setRenaissanceEvent(true);
        $event5->cancel();

        $event6 = new DefaultEvent(Uuid::fromString(self::EVENT_6_UUID));
        $event6->setName('Un événement de l\'assemblée départementale');
        $event6->setDescription('Description de l\'événement de l\'assemblée départementale');
        $event6->setCategory($eventCategory7);
        $event6->setPublished(true);
        $event6->setBeginAt((new \DateTime('now'))->modify('+6 hours'));
        $event6->setFinishAt((new \DateTime('now'))->modify('+8 hours'));
        $event6->setCapacity(50);
        $event6->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event6->setMode(BaseEvent::MODE_ONLINE);
        $event6->setTimeZone('Europe/Paris');
        $event6->setVisioUrl('https://parti-renaissance.fr');
        $event6->setOrganizer($adherent5);
        $event6->setPostAddress($this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684));
        $event6->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $event6->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92014'));
        $event6->incrementParticipantsCount(2);
        $event6->setRenaissanceEvent(true);

        $event7 = new DefaultEvent(Uuid::fromString(self::EVENT_7_UUID));
        $event7->setName('Un événement de l\'assemblée départementale passé');
        $event7->setDescription('Description de l\'événement de l\'assemblée départementale passé');
        $event7->setCategory($eventCategory7);
        $event7->setPublished(true);
        $event7->setBeginAt((new \DateTime('now'))->modify('-2 months'));
        $event7->setFinishAt((new \DateTime('now'))->modify('-2 months'));
        $event7->setCapacity(50);
        $event7->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event7->setMode(BaseEvent::MODE_ONLINE);
        $event7->setTimeZone('Europe/Paris');
        $event7->setOrganizer($adherent5);
        $event7->setPostAddress($this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684));
        $event7->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $event7->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92014'));
        $event7->incrementParticipantsCount(3);
        $event7->setRenaissanceEvent(true);

        $manager->persist($event1);
        $manager->persist($event2);
        $manager->persist($event3);
        $manager->persist($event4);
        $manager->persist($event5);
        $manager->persist($event6);
        $manager->persist($event7);

        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $this->getReference('adherent-7'))));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $this->getReference('user-1'))));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event5, $adherent5)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event6, $adherent5)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event6, $pad92)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event7, $adherent5)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event7, $adherentRe4)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event7, $pad92)));
        $eventRegistration1 = new EventRegistrationCommand($event1);
        $eventRegistration1->setFirstName('Marie');
        $eventRegistration1->setLastName('CLAIRE');
        $eventRegistration1->setEmailAddress('marie.claire@test.com');
        $manager->persist($this->eventRegistrationFactory->createFromCommand($eventRegistration1));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $this->getReference('adherent-7'))));

        for ($i = 1; $i <= 5; ++$i) {
            $manager->persist($event = new DefaultEvent());

            $event->setName('Event interne '.$i);
            $event->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
            $event->setPublished(true);
            $event->setBeginAt((new \DateTime('now'))->modify('+2 hours'));
            $event->setFinishAt((new \DateTime('now'))->modify('+4 hours'));
            $event->setCapacity(50);
            $event->setStatus(BaseEvent::STATUS_SCHEDULED);
            $event->setMode(0 === $i % 2 ? BaseEvent::MODE_MEETING : BaseEvent::MODE_ONLINE);
            $event->setTimeZone('Europe/Paris');
            $event->visibility = EventVisibilityEnum::PRIVATE;
            $event->setOrganizer($senatorialCandidate);
            $event->setPostAddress($this->createPostAddress('74 Avenue des Champs-Élysées, 75008 Paris', '75008-75108', null, 48.862725, 2.287592));
            $event->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
            $event->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadUserData::class,
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadReferentTagsZonesLinksData::class,
        ];
    }
}
