<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Event\EventFactory;
use AppBundle\Committee\Feed\CommitteeEvent;
use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event as EntityEvent;
use AppBundle\Entity\PostAddress;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadEventData implements FixtureInterface, ContainerAwareInterface
{
    const EVENT_1_UUID = '1fc69fd0-2b34-4bd4-a0cc-834480480934';
    const EVENT_2_UUID = 'defd812f-265c-4196-bd33-72fe39e5a2a1';
    const EVENT_3_UUID = '47e5a8bf-8be1-4c38-aae8-b41e6908a1b3';
    const EVENT_4_UUID = '5f10be0f-184b-47b8-9e45-39b9ec46f079';
    const EVENT_5_UUID = '24a01f4f-94ea-43eb-8601-579385c59a82';
    const EVENT_6_UUID = '5ba0daee-d9a7-47a8-8dbb-454500284af8';
    const EVENT_7_UUID = '00871ce7-21bd-448c-9775-a23b808e1666';
    const EVENT_8_UUID = '113876dd-87d2-426a-a12a-60ffd5107b10';
    const EVENT_9_UUID = '633d4cdf-c7b9-4188-ad7a-96d18e80bc09';
    const EVENT_10_UUID = '5c2471c7-8def-4757-9bec-8e0fa24361d8';
    const EVENT_11_UUID = 'f48c4863-dc9b-404c-8fd5-72b1c002788c';
    const EVENT_12_UUID = '50dd58d7-f370-4874-8fbb-3746ca06d5d5';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $author3 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $author7 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_7_UUID);
        $author11 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_11_UUID);
        $author12 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_12_UUID);

        $committee1 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $committee2 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);
        $committee3 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_3_UUID);
        $committee4 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_4_UUID);
        $committee5 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_5_UUID);

        // Singapore
        $committee8 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_8_UUID);

        // New York
        $committee9 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_9_UUID);

        $committeeEventFactory = $this->getEventFactory();
        $registrationFactory = $this->getEventRegistrationFactory();

        $event1 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_1_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Réunion de réflexion parisienne',
            'category' => 'CE005',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.870507, 2.303243),
            'begin_at' => date('Y-m-d', strtotime('+3 days')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('+3 days')).' 19:00:00',
            'capacity' => 50,
        ]);
        $event1->incrementParticipantsCount();

        $event2 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_2_UUID,
            'organizer' => $author7,
            'committee' => $committee3,
            'name' => 'Réunion de réflexion dammarienne',
            'category' => 'CE001',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', 48.518219, 2.622016),
            'begin_at' => date('Y-m-d', strtotime('+10 days')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('+ 10 days')).' 19:00:00',
            'capacity' => 50,
        ]);
        $event2->incrementParticipantsCount();
        $event2->incrementParticipantsCount();

        $event3 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_3_UUID,
            'organizer' => $author7,
            'committee' => $committee4,
            'name' => 'Réunion de réflexion bellifontaine',
            'category' => 'CE002',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', 48.404765, 2.698759),
            'begin_at' => date('Y-m-d', strtotime('tomorrow')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('tomorrow')).' 19:00:00',
            'capacity' => 50,
        ]);
        $event3->incrementParticipantsCount();

        $event4 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_4_UUID,
            'organizer' => $author7,
            'committee' => $committee5,
            'name' => 'Réunion de réflexion évryenne',
            'category' => 'CE003',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', 48.624157, 2.4266),
            'begin_at' => date('Y-m-d', strtotime('+15 days')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('+15 days')).' 19:00:00',
            'capacity' => 50,
        ]);
        $event4->incrementParticipantsCount();

        $event5 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_5_UUID,
            'organizer' => $author7,
            'committee' => $committee2,
            'name' => 'Réunion de réflexion marseillaise',
            'category' => 'CE006',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('2 Place de la Major', '13002-13202', 43.2984913, 5.3623771),
            'begin_at' => date('Y-m-d', strtotime('+17 days')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('+17 days')).' 19:00:00',
            'capacity' => 1,
        ]);
        $event5->incrementParticipantsCount();

        $event6 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_6_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Réunion de réflexion parisienne annulé',
            'category' => 'CE005',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.870507, 2.303243),
            'begin_at' => date('Y-m-d', strtotime('+60 days')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('+60 days')).' 19:00:00',
            'capacity' => 50,
        ]);
        $event6->cancel();
        $event6->incrementParticipantsCount();

        $event7 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_7_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Grand Meeting de Paris',
            'category' => 'CE005',
            'description' => 'Unissons nos forces pour la Présidentielle !',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.870507, 2.303243),
            'begin_at' => '2017-02-20 09:30:00',
            'finish_at' => '2017-02-20 19:00:00',
            'capacity' => 2000,
        ]);
        $event7->incrementParticipantsCount();

        $event8 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_8_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Grand Meeting de Marseille',
            'category' => 'CE005',
            'description' => 'Unissons nos forces pour la Présidentielle !',
            'address' => PostAddress::createFrenchAddress('2 Place de la Major', '13002-13202', 43.2984913, 5.3623771),
            'begin_at' => '2017-02-20 09:30:00',
            'finish_at' => '2017-02-20 19:00:00',
            'capacity' => 2000,
        ]);
        $event8->incrementParticipantsCount();

        $event9 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_9_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Marche Parisienne',
            'category' => 'CE010',
            'description' => 'Allons à la rencontre des citoyens.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.870507, 2.303243),
            'begin_at' => '2017-03-07 12:30:00',
            'finish_at' => '2017-03-07 17:30:00',
            'capacity' => 20,
        ]);
        $event9->incrementParticipantsCount();

        $event10 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_10_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Grand débat parisien',
            'category' => 'CE010',
            'description' => 'Débatons ensemble du programme.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.870507, 2.303243),
            'begin_at' => date('Y-m-d', strtotime('yesterday')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('yesterday')).' 19:00:00',
            'capacity' => 100,
        ]);
        $event10->incrementParticipantsCount();

        $event11 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_11_UUID,
            'organizer' => $author11,
            'committee' => $committee8,
            'name' => 'Meeting de Singapour',
            'category' => 'CE010',
            'description' => 'Ouvert aux français de Singapour.',
            'address' => PostAddress::createForeignAddress('SG', '018956', 'Singapour', '10 Bayfront Avenue', 1.2835627, 103.8606872),
            'begin_at' => (new \DateTime('now', new \DateTimeZone('Asia/Singapore')))->modify('-4 hours')->format('Y-m-d H:00:00'),
            'finish_at' => (new \DateTime('now', new \DateTimeZone('Asia/Singapore')))->modify('-2 hours')->format('Y-m-d H:00:00'),
            'capacity' => 100,
        ]);
        $event11->incrementParticipantsCount(2);

        $event12 = $committeeEventFactory->createFromArray([
            'uuid' => self::EVENT_12_UUID,
            'organizer' => $author12,
            'committee' => $committee9,
            'name' => 'Meeting de New York City',
            'category' => 'CE010',
            'description' => 'Ouvert aux français de New York.',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 40.7625289, -73.9859927),
            'begin_at' => (new \DateTime('now', new \DateTimeZone('America/New_York')))->modify('+10 hours')->format('Y-m-d H:00:00'),
            'finish_at' => (new \DateTime('now', new \DateTimeZone('America/New_York')))->modify('+13 hours')->format('Y-m-d H:00:00'),
            'capacity' => 55,
        ]);
        $event12->incrementParticipantsCount(2);

        $manager->persist($event1);
        $manager->persist($event2);
        $manager->persist($event3);
        $manager->persist($event4);
        $manager->persist($event5);
        $manager->persist($event6);
        $manager->persist($event7);
        $manager->persist($event8);
        $manager->persist($event9);
        $manager->persist($event10);
        $manager->persist($event11);
        $manager->persist($event12);

        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event8, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event9, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event10, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event1, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event2, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event2, $author7)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event3, $author7)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event4, $author7)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event5, $author7)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event6, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event7, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event11, $author11)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event11, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event12, $author12)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($event12, $author3)));

        $manager->flush();

        foreach ($this->getCommitteeMessageData($committee1) as $data) {
            $this->publishCommitteeMessage($committee1, $author3, $data['text'], $data['created_at']);
        }

        foreach ($this->getCommitteeMessageData($committee3) as $data) {
            $this->publishCommitteeMessage($committee3, $author3, $data['text'], $data['created_at']);
        }

        for ($day = 1; $day <= 31; ++$day) {
            $this->publishCommitteeMessage($committee1, $author3, sprintf("Rapport d'activité du %u janvier 2017.", $day), sprintf('2017-01-%02u 09:00:00', $day));
        }

        $this->publishCommitteeEvent($event1);
    }

    private function publishCommitteeMessage(Committee $committee, Adherent $author, string $text, string $createdAt = 'now')
    {
        return $this->getCommitteeFeedManager()->createMessage(new CommitteeMessage($author, $committee, $text, $createdAt));
    }

    private function publishCommitteeEvent(EntityEvent $event)
    {
        return $this->getCommitteeFeedManager()->createEvent(new CommitteeEvent($event->getOrganizer(), $event));
    }

    private function getCommitteeMessageData(Committee $committee): \Generator
    {
        $uuid = (string) $committee->getUuid();

        if (LoadAdherentData::COMMITTEE_1_UUID === $uuid) {
            yield [
                'text' => 'Ouverture du comité !',
                'created_at' => '2017-01-12 20:13:26',
            ];
            yield [
                'text' => "Comment ça va aujourd'hui les Marcheurs ?",
                'created_at' => '2017-01-13 08:31:12',
            ];
            yield [
                'text' => 'Tout le monde est prêt pour le porte à porte ?',
                'created_at' => '2017-01-13 10:08:45',
            ];
            yield [
                'text' => 'Réunion écologiste en préparation !',
                'created_at' => '2017-01-14 11:14:54',
            ];
            yield [
                'text' => "Visite d'Émmanuel Macron le 20 janvier.",
                'created_at' => '2017-01-15 13:28:33',
            ];
        }

        if ($uuid === LoadAdherentData::COMMITTEE_3_UUID) {
            yield [
                'text' => 'Lancement du comité !',
                'created_at' => '2017-01-16 13:14:56',
            ];
            yield [
                'text' => 'À la recherche de volontaires !',
                'created_at' => '2017-01-17 20:02:21',
            ];
        }
    }

    private function getCommitteeFeedManager(): CommitteeFeedManager
    {
        return $this->container->get('app.committee.feed_manager');
    }

    private function getEventFactory(): EventFactory
    {
        return $this->container->get('app.event.factory');
    }

    private function getEventRegistrationFactory(): EventRegistrationFactory
    {
        return $this->container->get('app.event.registration_factory');
    }
}
