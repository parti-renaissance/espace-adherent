<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTerritorialCouncilMembershipData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-3'), new \DateTime('2020-06-06'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DESIGNATED_ADHERENT, 'Super comité de Paris'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-4'), new \DateTime('2020-07-07'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::LRE_MANAGER, 'Paris 75'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-8'), new \DateTime('2020-03-03'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::REFERENT, '75'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-19'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Paris en marche!'));
        $manager->persist($membership);

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
