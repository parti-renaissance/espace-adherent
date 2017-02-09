<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Committee\Event\CommitteeEventFactory;
use AppBundle\Committee\Feed\CommitteeEvent;
use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeEvent as EntityCommitteeEvent;
use AppBundle\Entity\PostAddress;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCommitteeEventData implements FixtureInterface, ContainerAwareInterface
{
    const COMMITTEE_EVENT_1_UUID = '1fc69fd0-2b34-4bd4-a0cc-834480480934';
    const COMMITTEE_EVENT_2_UUID = 'defd812f-265c-4196-bd33-72fe39e5a2a1';
    const COMMITTEE_EVENT_3_UUID = '47e5a8bf-8be1-4c38-aae8-b41e6908a1b3';
    const COMMITTEE_EVENT_4_UUID = '5f10be0f-184b-47b8-9e45-39b9ec46f079';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $author3 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $author7 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_7_UUID);

        $committee1 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $committee2 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);
        $committee3 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_3_UUID);
        $committee4 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_4_UUID);
        $committee5 = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_5_UUID);

        $committeeEventFactory = $this->getCommitteeEventFactory();

        $event1 = $committeeEventFactory->createFromArray([
            'uuid' => self::COMMITTEE_EVENT_1_UUID,
            'organizer' => $author3,
            'committee' => $committee1,
            'name' => 'Réunion de réflexion parisienne',
            'category' => 'CE005',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.870507, 2.303243),
            'begin_at' => date('Y-m-d', strtotime('tomorrow')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('tomorrow')).' 19:00:00',
            'capacity' => 50,
        ]);

        $event2 = $committeeEventFactory->createFromArray([
            'uuid' => self::COMMITTEE_EVENT_2_UUID,
            'organizer' => $author7,
            'committee' => $committee3,
            'name' => 'Réunion de réflexion dammarienne',
            'category' => 'CE001',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', 48.518219, 2.622016),
            'begin_at' => date('Y-m-d', strtotime('tomorrow')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('tomorrow')).' 19:00:00',
            'capacity' => 50,
        ]);

        $event3 = $committeeEventFactory->createFromArray([
            'uuid' => self::COMMITTEE_EVENT_3_UUID,
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

        $event4 = $committeeEventFactory->createFromArray([
            'uuid' => self::COMMITTEE_EVENT_4_UUID,
            'organizer' => $author7,
            'committee' => $committee5,
            'name' => 'Réunion de réflexion évryenne',
            'category' => 'CE003',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', 48.624157, 2.4266),
            'begin_at' => date('Y-m-d', strtotime('tomorrow')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('tomorrow')).' 19:00:00',
            'capacity' => 50,
        ]);

        $manager->persist($event1);
        $manager->persist($event2);
        $manager->persist($event3);
        $manager->persist($event4);
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

    private function publishCommitteeEvent(EntityCommitteeEvent $event)
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

    private function getCommitteeEventFactory(): CommitteeEventFactory
    {
        return $this->container->get('app.committee_event_factory');
    }
}
