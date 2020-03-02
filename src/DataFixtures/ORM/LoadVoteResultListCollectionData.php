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
        $listCollection->mergeCities([$this->getReference('city-lille')]);

        $listCollection->addList(new VoteResultList('Liste 1'));
        $listCollection->addList(new VoteResultList('Liste 2'));
        $listCollection->addList(new VoteResultList('Liste 3'));

        $manager->persist($listCollection);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCityData::class,
        ];
    }
}
