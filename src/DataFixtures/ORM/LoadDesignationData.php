<?php

namespace App\DataFixtures\ORM;

use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Designation\DesignationZoneEnum;
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
        $designation->setCandidacyEndDate(new \DateTime('+1 week'));
        $designation->setVoteStartDate(new \DateTime('+1 week'));
        $designation->setVoteEndDate(new \DateTime('+4 week'));

        $this->setReference('designation-1', $designation);
        $manager->persist($designation);

        $designation = new Designation();
        $designation->setZones([DesignationZoneEnum::FRANCE]);
        $designation->setType(DesignationTypeEnum::COMMITTEE_ADHERENT);
        $designation->setCandidacyStartDate(new \DateTime('-1 month'));
        $designation->setCandidacyEndDate(new \DateTime('-2 hours'));
        $designation->setVoteStartDate(new \DateTime('-1 hour'));
        $designation->setVoteEndDate(new \DateTime('+4 week'));

        $this->setReference('designation-2', $designation);
        $manager->persist($designation);

        $designation = new Designation();
        $designation->setZones([DesignationZoneEnum::FRANCE]);
        $designation->setType(DesignationTypeEnum::COMMITTEE_ADHERENT);
        $designation->setCandidacyStartDate(new \DateTime('-1 month'));
        $designation->setCandidacyEndDate(new \DateTime('-1 week'));
        $designation->setVoteStartDate(new \DateTime('-6 days'));
        $designation->setVoteEndDate(new \DateTime('-1 hour'));

        $this->setReference('designation-3', $designation);
        $manager->persist($designation);

        $designation = new Designation();
        $designation->setZones([DesignationZoneEnum::FRANCE]);
        $designation->setType(DesignationTypeEnum::COMMITTEE_ADHERENT);
        $designation->setCandidacyStartDate(new \DateTime('-6 months'));
        $designation->setCandidacyEndDate(new \DateTime('-5 months'));
        $designation->setVoteStartDate(new \DateTime('-5 months'));
        $designation->setVoteEndDate(new \DateTime('-4 months'));

        $this->setReference('designation-4', $designation);
        $manager->persist($designation);

        $manager->flush();
    }
}
