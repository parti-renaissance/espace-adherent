<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Election\MinistryListTotalResult;
use AppBundle\Entity\Election\MinistryVoteResult;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMinistryVoteResultData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $result = new MinistryVoteResult($this->getReference('city-lille'), $this->getReference('round-1-legislatives'));
        $result->setRegistered(1000);
        $result->setParticipated(666);
        $result->setExpressed(660);
        $result->setAbstentions(140);

        $list = new MinistryListTotalResult();
        $list->setAdherentCount(10);
        $list->setEligibleCount(7);
        $list->setLabel('Liste 1');
        $list->setNuance('REM');
        $list->setTotal(5);
        $list->setPosition(1);
        $list->setCandidateFirstName('Michel');
        $list->setCandidateLastName('Dupont');

        $result->addListTotalResult($list);

        $manager->persist($result);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadElectionData::class,
            LoadCityData::class,
        ];
    }
}
