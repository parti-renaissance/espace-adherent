<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Election\VoteResultList;
use AppBundle\Entity\Election\VoteResultListCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadVoteResultListCollectionData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $listCollection = new VoteResultListCollection();
        $listCollection->setCity($this->getReference('city-lille'));

        $listCollection->addList($this->createList('Liste 1', 'REM', 1));
        $listCollection->addList($this->createList('Liste 2', '', 2));
        $listCollection->addList($this->createList('Liste 3', '', 3));

        $manager->persist($listCollection);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCityData::class,
        ];
    }

    private function createList(string $label, string $nuance, int $position): VoteResultList
    {
        $list = new VoteResultList();

        $list->setLabel($label);
        $list->setNuance($nuance);
        $list->setPosition($position);

        return $list;
    }
}
