<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Committee\Event\CommitteeEventFactory;
use AppBundle\Committee\Feed\CommitteeEvent;
use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\PostAddress;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCommitteeEventData implements FixtureInterface, ContainerAwareInterface
{
    const COMMITTEE_EVENT_1_UUID = '1fc69fd0-2b34-4bd4-a0cc-834480480934';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $author = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $committee = $manager->getRepository(Committee::class)->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);

        $committeeEventFactory = $this->getCommitteeEventFactory();

        $event = $committeeEventFactory->createFromArray([
            'uuid' => self::COMMITTEE_EVENT_1_UUID,
            'organizer' => $author,
            'committee' => $committee,
            'name' => 'Réunion de réflexion',
            'category' => 'CE005',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', 45.570898, 5.927206),
            'begin_at' => date('Y-m-d', strtotime('tomorrow')).' 09:30:00',
            'finish_at' => date('Y-m-d', strtotime('tomorrow')).' 19:00:00',
            'capacity' => 50,
        ]);

        $manager->persist($event);
        $manager->flush();

        $feedManager = $this->getCommitteeFeedManager();
        foreach ($this->getCommitteeMessageData() as $data) {
            $feedManager->createMessage(new CommitteeMessage($author, $committee, $data['text'], $data['created_at']));
        }

        for ($day = 1; $day <= 31; ++$day) {
            $feedManager->createMessage(new CommitteeMessage(
                $author,
                $committee,
                sprintf("Rapport d'activité du %u janvier 2017.", $day),
                sprintf('2017-01-%02u 09:00:00', $day))
            );
        }

        $feedManager->createEvent(new CommitteeEvent($author, $event));
    }

    private function getCommitteeMessageData(): \Generator
    {
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

    private function getCommitteeFeedManager(): CommitteeFeedManager
    {
        return $this->container->get('app.committee.feed_manager');
    }

    private function getCommitteeEventFactory(): CommitteeEventFactory
    {
        return $this->container->get('app.committee_event_factory');
    }
}
