<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaCategoryData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $europeanCategory = new Category(
            'Européenne',
            true
        );

        $this->addReference('category-european', $europeanCategory);

        $nationalCategory = new Category(
            'National',
            true
        );

        $this->addReference('category-national', $nationalCategory);

        $localCategory = new Category(
            'Local',
            true
        );

        $this->addReference('category-local', $localCategory);

        $notPublishedCategory = new Category(
            'Not published category'
        );

        $this->addReference('category-not-published', $notPublishedCategory);

        $manager->persist($europeanCategory);
        $manager->persist($nationalCategory);
        $manager->persist($localCategory);
        $manager->persist($notPublishedCategory);

        $manager->flush();
    }
}
