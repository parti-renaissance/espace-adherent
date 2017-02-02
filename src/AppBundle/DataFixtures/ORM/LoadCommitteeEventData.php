<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Committee\Event\CommitteeEventFactory;
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

    private $em;

    public function load(ObjectManager $manager)
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $author = $this->em->getRepository(Adherent::class)->findOneBy(['uuid' => LoadAdherentData::ADHERENT_3_UUID]);
        $committee = $this->em->getRepository(Committee::class)->findOneBy(['uuid' => LoadAdherentData::COMMITTEE_1_UUID]);

        $committeeEventFactory = $this->getCommitteeEventFactory();

        $event = $committeeEventFactory->createFromArray([
            'uuid' => self::COMMITTEE_EVENT_1_UUID,
            'author' => $author->getUuid(),
            'committee' => $committee,
            'name' => 'Réunion de réflexion',
            'category' => 'Réu',
            'description' => 'Nous allons échanger autour de différents sujets',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', 45.570898, 5.927206),
            'begin_at' => 'now',
            'finish_at' => 'now',
            'capacity' => 50,
        ]);

        $manager->persist($event);
        $manager->flush();
    }

    private function getCommitteeEventFactory(): CommitteeEventFactory
    {
        return $this->container->get('app.committee_event_factory');
    }
}
