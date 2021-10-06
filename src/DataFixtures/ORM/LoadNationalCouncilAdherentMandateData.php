<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentMandate\NationalCouncilAdherentMandate;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadNationalCouncilAdherentMandateData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        $manager->persist(NationalCouncilAdherentMandate::create(
            $coTerrParis,
            $this->getReference('adherent-3'),
            new \DateTime('2020-06-06')
        ));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadTerritorialCouncilData::class,
        ];
    }
}
