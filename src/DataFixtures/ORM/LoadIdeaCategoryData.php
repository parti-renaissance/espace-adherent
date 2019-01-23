<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\IdeasWorkshop\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaCategoryData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_category');

        $europeanCategory = new Category(
            'Echelle Européenne',
            true
        );

        $this->addReference('category-european', $europeanCategory);

        $nationalCategory = new Category(
            'Echelle Nationale',
            true
        );

        $this->addReference('category-national', $nationalCategory);

        $localCategory = new Category(
            'Echelle Locale',
            true
        );

        $this->addReference('category-local', $localCategory);

        $notPublishedCategory = new Category(
            'Echelle non publiée'
        );

        $this->addReference('category-not-published', $notPublishedCategory);

        $manager->persist($europeanCategory);
        $manager->persist($nationalCategory);
        $manager->persist($localCategory);
        $manager->persist($notPublishedCategory);

        $manager->flush();
    }
}
