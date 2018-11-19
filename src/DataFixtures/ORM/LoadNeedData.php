<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Need;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadNeedData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $legalNeed = new Need(
            'Juridique',
            true
        );

        $this->addReference('need-legal', $legalNeed);

        $notPublishedNeed = new Need(
            'Besoin non publié'
        );

        $this->addReference('need-not-published', $notPublishedNeed);

        $manager->persist($legalNeed);
        $manager->persist($notPublishedNeed);

        $manager->flush();
    }
}
