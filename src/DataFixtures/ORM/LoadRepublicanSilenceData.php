<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\RepublicanSilence;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadRepublicanSilenceData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $entity = new RepublicanSilence();
        $entity->setZones([75]);
        $entity->setBeginAt((new \DateTime())->modify('-10 days'));
        $entity->setFinishAt((new \DateTime())->modify('+10 days'));

        $manager->persist($entity);
        $manager->flush();
    }
}
