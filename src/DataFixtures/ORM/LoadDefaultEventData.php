<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\DefaultEvent;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDefaultEventData extends AbstractFixtures implements DependentFixtureInterface
{
    private const EVENT_1_UUID = '5cab27a7-dbb3-4347-9781-566dad1b9eb5';
    private const EVENT_2_UUID = '2b7238f9-10ca-4a39-b8a4-ad7f438aa95f';

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

        $manager->persist($event1);
        $manager->persist($event2);

        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event1, $this->getReference('adherent-7'))));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event2, $this->getReference('adherent-7'))));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadEventCategoryData::class,
        ];
    }
}
