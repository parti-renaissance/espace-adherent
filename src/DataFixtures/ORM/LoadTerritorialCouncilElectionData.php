<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTerritorialCouncilElectionData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');
        $coTerrParis->setCurrentElection($election = new Election($this->getReference('designation-6')));
        $election->setElectionMode(DesignationVoteModeEnum::VOTE_MODE_ONLINE);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadDesignationData::class,
            LoadTerritorialCouncilData::class,
        ];
    }
}
