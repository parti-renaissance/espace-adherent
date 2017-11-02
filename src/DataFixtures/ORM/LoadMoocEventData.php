<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Event\EventFactory;
use AppBundle\Entity\PostAddress;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationFactory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadMoocEventData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    const MOOC_EVENT_1_UUID = '3f46976e-e76a-476e-86d7-575c6d3bc15f';
    const MOOC_EVENT_2_UUID = '36ab5f85-5feb-4ff7-8218-d2da63045b74';
    const MOOC_EVENT_3_UUID = '0389f98c-3e6c-4c92-ba80-19ab4a73e34b';
    const MOOC_EVENT_4_UUID = '39f25bd2-f866-4c0d-84da-2387898b8db1';
    const MOOC_EVENT_5_UUID = '92c8c6c9-928e-4686-8431-2e09254feb77';
    const MOOC_EVENT_6_UUID = '0e533344-47cd-4dc4-909d-59f8cda21006';
    const MOOC_EVENT_7_UUID = 'b5236488-a534-459a-a1a3-6df85b7c5ad9';
    const MOOC_EVENT_8_UUID = 'a02ca3c7-1b07-46cd-81b2-d309258e53f9';
    const MOOC_EVENT_9_UUID = '8d8a8ce7-ba60-4750-a300-bf0356e0ae0f';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $eventFactory = $this->getEventFactory();
        $registrationFactory = $this->getEventRegistrationFactory();

        $moocEvent1 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_1_UUID,
            'organizer' => $this->getReference('adherent-1'),
            'name' => 'Séance MOOC de Zürich',
            'category' => $this->getReference('mooc-event-category-1'),
            'description' => 'Une séance MOOC pour les Zurichois(es)',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '30 Zeppelinstrasse', 47.3950062, 8.53838),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 19:00:00'),
            'capacity' => 20,
        ]);
        $moocEvent1->setPublished(true);
        $moocEvent1->setWasPublished(true);
        $moocEvent1->incrementParticipantsCount();

        $moocEvent2 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_2_UUID,
            'organizer' => $this->getReference('adherent-2'),
            'name' => 'Séance MOOC-CI',
            'category' => $this->getReference('mooc-event-category-2'),
            'description' => 'Séance MOOC à Mouxy',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', 45.7218703, 5.929463),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+9 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+ 9 days')).' 19:00:00'),
            'capacity' => 30,
        ]);
        $moocEvent2->setPublished(false);
        $moocEvent2->setWasPublished(true);
        $moocEvent2->incrementParticipantsCount();

        $moocEvent3 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_3_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Séance MOOC #3',
            'category' => $this->getReference('mooc-event-category-3'),
            'description' => 'Une troisième séance MOOC',
            'address' => PostAddress::createFrenchAddress('16 rue de la Paix', '75008-75108', 48.869331, 2.331595),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 16:00:00'),
            'capacity' => 20,
        ]);
        $moocEvent3->setPublished(true);
        $moocEvent3->setWasPublished(true);
        $moocEvent3->incrementParticipantsCount();

        $moocEvent4 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_4_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Séance MOOC Paris-18',
            'category' => $this->getReference('mooc-event-category-1'),
            'description' => 'Séance dans le 18è arrondissement',
            'address' => PostAddress::createFrenchAddress('26 rue de la Paix', '75008-75108', 48.869878, 2.332197),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 10:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 15:00:00'),
            'capacity' => 20,
        ]);
        $moocEvent4->setPublished(true);
        $moocEvent4->setWasPublished(true);
        $moocEvent4->incrementParticipantsCount();

        $moocEvent5 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_5_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Séance MOOC Kilchberg',
            'category' => $this->getReference('mooc-event-category-2'),
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 12:00:00'),
            'capacity' => 10,
        ]);
        $moocEvent5->setPublished(true);
        $moocEvent5->setWasPublished(true);
        $moocEvent5->incrementParticipantsCount();

        $moocEvent6 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_6_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Séance MOOC annulée',
            'category' => $this->getReference('mooc-event-category-1'),
            'description' => 'On a annulé cette séance MOOC.',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+20 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+20 days')).' 18:00:00'),
            'capacity' => 5,
        ]);
        $moocEvent6->setPublished(true);
        $moocEvent6->setWasPublished(true);
        $moocEvent6->cancel();
        $moocEvent6->incrementParticipantsCount();

        $moocEvent7 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_7_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Séance MOOC Kilchberg #2',
            'category' => $this->getReference('mooc-event-category-1'),
            'description' => 'Nous allons rendre notre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 12:00:00'),
            'capacity' => 10,
        ]);
        $moocEvent7->setPublished(true);
        $moocEvent7->setWasPublished(true);
        $moocEvent7->incrementParticipantsCount(10);

        $moocEvent8 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_8_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Séance MOOC Kilchberg non publiée',
            'category' => $this->getReference('mooc-event-category-1'),
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 12:00:00'),
            'capacity' => 10,
        ]);

        $moocEvent9 = $eventFactory->createMoocEventFromArray([
            'uuid' => self::MOOC_EVENT_9_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Séance MOOC du Vieux-Port',
            'category' => $this->getReference('mooc-event-category-1'),
            'description' => 'Nous allons rendre notre Vieux-Port propre',
            'address' => PostAddress::createForeignAddress('FR', '13001', 'Marseille', '25 Quai des Belges', 43.2943855, 5.3737235),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('-15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('-15 days')).' 12:00:00'),
            'capacity' => 10,
        ]);
        $moocEvent9->setPublished(true);
        $moocEvent9->setWasPublished(true);
        $moocEvent9->incrementParticipantsCount(2);

        $manager->persist($moocEvent1);
        $manager->persist($moocEvent2);
        $manager->persist($moocEvent3);
        $manager->persist($moocEvent4);
        $manager->persist($moocEvent5);
        $manager->persist($moocEvent6);
        $manager->persist($moocEvent7);
        $manager->persist($moocEvent8);
        $manager->persist($moocEvent9);

        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent1, $this->getReference('adherent-1'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent2, $this->getReference('adherent-2'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent3, $this->getReference('adherent-3'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent4, $this->getReference('adherent-3'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent5, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent6, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent7, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent8, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent9, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($moocEvent9, $this->getReference('adherent-13'))));

        $manager->flush();
    }

    private function getEventFactory(): EventFactory
    {
        return $this->container->get('app.event.factory');
    }

    private function getEventRegistrationFactory(): EventRegistrationFactory
    {
        return $this->container->get('app.event.registration_factory');
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadGroupData::class,
            LoadMoocEventCategoryData::class,
        ];
    }
}
