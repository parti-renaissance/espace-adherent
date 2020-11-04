<?php

namespace App\DataFixtures\ORM;

use App\Entity\CitizenActionCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenActionCategoryData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $category = new CitizenActionCategory('Action citoyenne');

        $this->addReference('citizen-action-category', $category);

        $manager->persist($category);
        $manager->flush();
    }
}
