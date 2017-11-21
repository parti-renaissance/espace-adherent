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

class LoadCitizenActionData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    const CITIZEN_ACTION_1_UUID = '3f46976e-e76a-476e-86d7-575c6d3bc15f';
    const CITIZEN_ACTION_2_UUID = '36ab5f85-5feb-4ff7-8218-d2da63045b74';
    const CITIZEN_ACTION_3_UUID = '0389f98c-3e6c-4c92-ba80-19ab4a73e34b';
    const CITIZEN_ACTION_4_UUID = '39f25bd2-f866-4c0d-84da-2387898b8db1';
    const CITIZEN_ACTION_5_UUID = '92c8c6c9-928e-4686-8431-2e09254feb77';
    const CITIZEN_ACTION_6_UUID = '0e533344-47cd-4dc4-909d-59f8cda21006';
    const CITIZEN_ACTION_7_UUID = 'b5236488-a534-459a-a1a3-6df85b7c5ad9';
    const CITIZEN_ACTION_8_UUID = 'a02ca3c7-1b07-46cd-81b2-d309258e53f9';
    const CITIZEN_ACTION_9_UUID = '8d8a8ce7-ba60-4750-a300-bf0356e0ae0f';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $eventFactory = $this->getEventFactory();
        $registrationFactory = $this->getEventRegistrationFactory();

        $citizenAction1 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_1_UUID,
            'citizen_project' => $this->getReference('citizen-project-3'),
            'organizer' => $this->getReference('adherent-1'),
            'name' => 'Projet citoyen de Zürich',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Un projet citoyen pour les Zurichois(es)',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '30 Zeppelinstrasse', 47.3950062, 8.53838),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 19:00:00'),
            'capacity' => 20,
        ]);
        $citizenAction1->setPublished(true);
        $citizenAction1->setWasPublished(true);
        $citizenAction1->incrementParticipantsCount();

        $citizenAction2 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_2_UUID,
            'citizen_project' => $this->getReference('citizen-project-2'),
            'organizer' => $this->getReference('adherent-2'),
            'name' => 'Projet citoyen',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Projet citoyen à Mouxy',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', 45.7218703, 5.929463),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+9 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+ 9 days')).' 19:00:00'),
            'capacity' => 30,
        ]);
        $citizenAction2->setPublished(false);
        $citizenAction2->setWasPublished(true);
        $citizenAction2->incrementParticipantsCount();

        $citizenAction3 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_3_UUID,
            'citizen_project' => $this->getReference('citizen-project-1'),
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Projet citoyen #3',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Un troisième projet citoyen',
            'address' => PostAddress::createFrenchAddress('16 rue de la Paix', '75008-75108', 48.869331, 2.331595),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 16:00:00'),
            'capacity' => 20,
        ]);
        $citizenAction3->setPublished(true);
        $citizenAction3->setWasPublished(true);
        $citizenAction3->incrementParticipantsCount();

        $citizenAction4 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_4_UUID,
            'citizen_project' => $this->getReference('citizen-project-1'),
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Projet citoyen Paris-18',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Séance dans le 18è arrondissement',
            'address' => PostAddress::createFrenchAddress('26 rue de la Paix', '75008-75108', 48.869878, 2.332197),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 10:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 15:00:00'),
            'capacity' => 20,
        ]);
        $citizenAction4->setPublished(true);
        $citizenAction4->setWasPublished(true);
        $citizenAction4->incrementParticipantsCount();

        $citizenAction5 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_5_UUID,
            'citizen_project' => $this->getReference('citizen-project-3'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen Kilchberg',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 12:00:00'),
            'capacity' => 10,
        ]);
        $citizenAction5->setPublished(true);
        $citizenAction5->setWasPublished(true);
        $citizenAction5->incrementParticipantsCount();

        $citizenAction6 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_6_UUID,
            'citizen_project' => $this->getReference('citizen-project-4'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen annulée',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'On a annulé ce projet citoyen.',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+20 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+20 days')).' 18:00:00'),
            'capacity' => 5,
        ]);
        $citizenAction6->setPublished(true);
        $citizenAction6->setWasPublished(true);
        $citizenAction6->cancel();
        $citizenAction6->incrementParticipantsCount();

        $citizenAction7 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_7_UUID,
            'citizen_project' => $this->getReference('citizen-project-9'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen Kilchberg #2',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre notre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 12:00:00'),
            'capacity' => 10,
        ]);
        $citizenAction7->setPublished(true);
        $citizenAction7->setWasPublished(true);
        $citizenAction7->incrementParticipantsCount(10);

        $citizenAction8 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_8_UUID,
            'citizen_project' => $this->getReference('citizen-project-9'),
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Projet citoyen Kilchberg non publiée',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 12:00:00'),
            'capacity' => 10,
        ]);

        $citizenAction9 = $eventFactory->createCitizenActionFromArray([
            'uuid' => self::CITIZEN_ACTION_9_UUID,
            'citizen_project' => $this->getReference('citizen-project-2'),
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Projet citoyen du Vieux-Port',
            'category' => $this->getReference('citizen-action-category'),
            'description' => 'Nous allons rendre notre Vieux-Port propre',
            'address' => PostAddress::createForeignAddress('FR', '13001', 'Marseille', '25 Quai des Belges', 43.2943855, 5.3737235),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('-15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('-15 days')).' 12:00:00'),
            'capacity' => 10,
        ]);
        $citizenAction9->setPublished(true);
        $citizenAction9->setWasPublished(true);
        $citizenAction9->incrementParticipantsCount(2);

        $manager->persist($citizenAction1);
        $manager->persist($citizenAction2);
        $manager->persist($citizenAction3);
        $manager->persist($citizenAction4);
        $manager->persist($citizenAction5);
        $manager->persist($citizenAction6);
        $manager->persist($citizenAction7);
        $manager->persist($citizenAction8);
        $manager->persist($citizenAction9);

        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction1, $this->getReference('adherent-1'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction2, $this->getReference('adherent-2'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction3, $this->getReference('adherent-3'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction4, $this->getReference('adherent-3'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction5, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction6, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction7, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction8, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction9, $this->getReference('adherent-13'))));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($citizenAction9, $this->getReference('adherent-13'))));

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
            LoadCitizenProjectData::class,
            LoadCitizenActionCategoryData::class,
        ];
    }
}
