<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventCategory;
use App\Event\EventRegistrationCommand;
use App\Event\EventVisibilityEnum;
use App\Scope\ScopeEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadEventData extends AbstractLoadEventData implements DependentFixtureInterface
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
        $referent = $this->getReference('adherent-8', Adherent::class);
        $senatorialCandidate = $this->getReference('senatorial-candidate', Adherent::class);
        $adherent5 = $this->getReference('adherent-5', Adherent::class);
        $adherentRe4 = $this->getReference('renaissance-user-4', Adherent::class);
        $pad92 = $this->getReference('president-ad-1', Adherent::class);

        $eventCategory7 = $this->getReference('CE007', EventCategory::class);

        $event1 = new Event(Uuid::fromString(self::EVENT_1_UUID));
        $event1->setName('Nouvel événement online');
        $event1->setDescription('Description du nouvel événement online');
        $event1->setPublished(true);
        $event1->setBeginAt((new \DateTime('now'))->modify('+1 hour'));
        $event1->setFinishAt((new \DateTime('now'))->modify('+2 hours'));
        $event1->setCapacity(50);
        $event1->setStatus(Event::STATUS_SCHEDULED);
        $event1->setMode(Event::MODE_ONLINE);
        $event1->setTimeZone('Europe/Paris');
        $event1->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $event1->setPostAddress($this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684));
        $event1->setAuthor($referent);
        $event1->setAuthorInstance(ScopeEnum::SCOPE_INSTANCES[ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY]);
        $event1->setAuthorRole(ScopeEnum::ROLE_NAMES[ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY]);

        $event2 = new Event(Uuid::fromString(self::EVENT_2_UUID));
        $event2->setName('Nouvel événement online privé et électoral');
        $event2->setDescription('Description du nouvel événement online privé et électoral');
        $event2->setPublished(true);
        $event2->setBeginAt((new \DateTime('now'))->modify('+2 hours'));
        $event2->setFinishAt((new \DateTime('now'))->modify('+3 hours'));
        $event2->setCapacity(50);
        $event2->setStatus(Event::STATUS_SCHEDULED);
        $event2->setMode(Event::MODE_ONLINE);
        $event2->setTimeZone('Europe/Paris');
        $event2->setAuthor($referent);
        $event2->visibility = EventVisibilityEnum::ADHERENT;
        $event2->setElectoral(true);
        $event2->setPostAddress($this->createPostAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
        $event2->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));

        $event3 = new Event(Uuid::fromString(self::EVENT_3_UUID));
        $event3->setName('Un événement du référent annulé');
        $event3->setDescription('Description de l\'événement du référent annulé');
        $event3->setPublished(true);
        $event3->setBeginAt((new \DateTime('now'))->modify('-2 hours'));
        $event3->setFinishAt((new \DateTime('now'))->modify('-1 hour'));
        $event3->setCapacity(50);
        $event3->setStatus(Event::STATUS_SCHEDULED);
        $event3->setMode(Event::MODE_ONLINE);
        $event3->setTimeZone('Europe/Paris');
        $event3->setAuthor($referent);
        $event3->setPostAddress($this->createPostAddress('40 Rue Grande', '77300-77186', null, 48.404765, 2.698759));
        $event3->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'));
        $event3->cancel();

        $event4 = new Event(Uuid::fromString(self::EVENT_4_UUID));
        $event4->setName('Un événement du candidat aux législatives');
        $event4->setDescription('Description de l\'événement du candidat aux législatives');
        $event4->setPublished(true);
        $event4->setBeginAt((new \DateTime('now'))->modify('+2 hours'));
        $event4->setFinishAt((new \DateTime('now'))->modify('+4 hours'));
        $event4->setCapacity(50);
        $event4->setStatus(Event::STATUS_SCHEDULED);
        $event4->setMode(Event::MODE_MEETING);
        $event4->setTimeZone('Europe/Paris');
        $event4->setAuthor($senatorialCandidate);
        $event4->setAuthorInstance(ScopeEnum::SCOPE_INSTANCES[ScopeEnum::LEGISLATIVE_CANDIDATE]);
        $event4->setAuthorRole(ScopeEnum::ROLE_NAMES[ScopeEnum::LEGISLATIVE_CANDIDATE]);
        $event4->setPostAddress($this->createPostAddress('74 Avenue des Champs-Élysées, 75008 Paris', '75008-75108', null, 48.862725, 2.287592));
        $event4->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $event4->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        $this->setReference('event-4', $event4);

        $event5 = new Event(Uuid::fromString(self::EVENT_5_UUID));
        $event5->setName('Un événement de l\'assemblée départementale annulé');
        $event5->setDescription('Description de l\'événement de l\'assemblée départementale annulé');
        $event5->setCategory($eventCategory7);
        $event5->setPublished(true);
        $event5->setBeginAt((new \DateTime('now'))->modify('-3 hours'));
        $event5->setFinishAt((new \DateTime('now'))->modify('-2 hour'));
        $event5->setCapacity(50);
        $event5->setStatus(Event::STATUS_SCHEDULED);
        $event5->setMode(Event::MODE_ONLINE);
        $event5->setTimeZone('Europe/Paris');
        $event5->setAuthor($adherent5);
        $event5->setPostAddress($this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684));
        $event5->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $event5->incrementParticipantsCount();
        $event5->cancel();

        $event6 = new Event(Uuid::fromString(self::EVENT_6_UUID));
        $event6->setName('Un événement de l\'assemblée départementale');
        $event6->setDescription('Description de l\'événement de l\'assemblée départementale');
        $event6->setCategory($eventCategory7);
        $event6->setPublished(true);
        $event6->setBeginAt((new \DateTime('now'))->modify('+6 hours'));
        $event6->setFinishAt((new \DateTime('now'))->modify('+8 hours'));
        $event6->setCapacity(50);
        $event6->setStatus(Event::STATUS_SCHEDULED);
        $event6->setMode(Event::MODE_ONLINE);
        $event6->setTimeZone('Europe/Paris');
        $event6->setVisioUrl('https://parti-renaissance.fr');
        $event6->setAuthor($adherent5);
        $event6->setPostAddress($this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684));
        $event6->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $event6->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92014'));
        $event6->incrementParticipantsCount(2);

        $event7 = new Event(Uuid::fromString(self::EVENT_7_UUID));
        $event7->setName('Un événement de l\'assemblée départementale passé');
        $event7->setDescription('Description de l\'événement de l\'assemblée départementale passé');
        $event7->setCategory($eventCategory7);
        $event7->setPublished(true);
        $event7->setBeginAt((new \DateTime('now'))->modify('-2 months'));
        $event7->setFinishAt((new \DateTime('now'))->modify('-2 months'));
        $event7->setCapacity(50);
        $event7->setStatus(Event::STATUS_SCHEDULED);
        $event7->setMode(Event::MODE_ONLINE);
        $event7->setTimeZone('Europe/Paris');
        $event7->setAuthor($adherent5);
        $event7->setPostAddress($this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684));
        $event7->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $event7->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92014'));
        $event7->incrementParticipantsCount(3);

        $manager->persist($event1);
        $manager->persist($event2);
        $manager->persist($event3);
        $manager->persist($event4);
        $manager->persist($event5);
        $manager->persist($event6);
        $manager->persist($event7);

        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $this->getReference('adherent-7', Adherent::class))));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $this->getReference('user-1', Adherent::class))));
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
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $this->getReference('adherent-7', Adherent::class))));

        for ($i = 1; $i <= 5; ++$i) {
            $event = new Event();
            $event->setName('Event interne '.$i);
            $event->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
            $event->setPublished(true);
            $event->setBeginAt((new \DateTime('now'))->modify('+2 hours'));
            $event->setFinishAt((new \DateTime('now'))->modify('+4 hours'));
            $event->setCapacity(50);
            $event->setStatus(Event::STATUS_SCHEDULED);
            $event->setMode(0 === $i % 2 ? Event::MODE_MEETING : Event::MODE_ONLINE);
            $event->setTimeZone('Europe/Paris');
            $event->visibility = EventVisibilityEnum::PRIVATE;
            $event->setAuthor($senatorialCandidate);
            $event->setPostAddress($this->createPostAddress('74 Avenue des Champs-Élysées, 75008 Paris', '75008-75108', null, 48.862725, 2.287592));
            $event->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
            $event->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadUserData::class,
            LoadAdherentData::class,
            LoadEventCategoryData::class,
        ];
    }
}
