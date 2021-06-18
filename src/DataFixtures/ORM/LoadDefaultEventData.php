<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\DefaultEvent;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDefaultEventData extends Fixture implements DependentFixtureInterface
{
    private const EVENT_1_UUID = '5cab27a7-dbb3-4347-9781-566dad1b9eb5';

    private $eventRegistrationFactory;

    public function __construct(EventRegistrationFactory $eventRegistrationFactory)
    {
        $this->eventRegistrationFactory = $eventRegistrationFactory;
    }

    public function load(ObjectManager $manager)
    {
        $referent = $this->getReference('adherent-8');

        $event = new DefaultEvent(Uuid::fromString(self::EVENT_1_UUID));
        $event->setName('Nouvel événement online');
        $event->setDescription('Description du nouvel événement online');
        $event->setPublished(true);
        $event->setBeginAt((new \DateTime('now'))->modify('+1 hour'));
        $event->setFinishAt((new \DateTime('now'))->modify('+2 hours'));
        $event->setCapacity(50);
        $event->setStatus(BaseEvent::STATUS_SCHEDULED);
        $event->setMode(BaseEvent::MODE_ONLINE);
        $event->setTimeZone('Europe/Paris');
        $event->setOrganizer($referent);

        $manager->persist($event);

        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event, $referent)));
        $manager->persist($this->eventRegistrationFactory->createFromCommand(new EventRegistrationCommand($event, $this->getReference('adherent-7'))));

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
