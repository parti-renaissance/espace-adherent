<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\VotingPlatform\Designation\Designation;
use AppBundle\VotingPlatform\Designation\DesignationTypeEnum;
use AppBundle\VotingPlatform\Designation\DesignationZoneEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDesignationData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $designation = new Designation();
        $designation->setZones([DesignationZoneEnum::FRANCE]);
        $designation->setType(DesignationTypeEnum::COMMITTEE_ADHERENT);
        $designation->setCandidacyStartDate(new \DateTime('-1 month'));
        $designation->setCandidacyEndDate(new \DateTime());
        $designation->setVoteStartDate(new \DateTime('+1 week'));
        $designation->setVoteEndDate(new \DateTime('+4 week'));

        $this->setReference('designation-1', $designation);
        $manager->persist($designation);

        $manager->flush();
    }
}
