<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\Need;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaNeedData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_need');

        $legalNeed = new Need(
            'Juridique',
            true
        );

        $this->addReference('need-legal', $legalNeed);

        $editorialNeed = new Need(
            'Rédactionnel',
            true
        );

        $this->addReference('need-editorial', $legalNeed);

        $notPublishedNeed = new Need(
            'Besoin non publié'
        );

        $this->addReference('need-not-published', $notPublishedNeed);

        $manager->persist($legalNeed);
        $manager->persist($editorialNeed);
        $manager->persist($notPublishedNeed);

        $manager->flush();
    }
}
