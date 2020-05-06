<?php

namespace App\DataFixtures\ORM;

use App\Entity\CitizenActionCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenActionCategoryData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $category = new CitizenActionCategory('Action citoyenne');

        $this->addReference('citizen-action-category', $category);

        $manager->persist($category);
        $manager->flush();
    }
}
