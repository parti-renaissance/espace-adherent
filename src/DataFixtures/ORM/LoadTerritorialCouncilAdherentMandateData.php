<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\ValueObject\Genders;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTerritorialCouncilAdherentMandateData extends AbstractFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        $manager->persist(TerritorialCouncilAdherentMandate::create(
            $coTerrParis,
            $this->getReference('adherent-3'),
            new \DateTime('2020-06-06'),
            Genders::MALE,
            TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
        ));

        $manager->persist(TerritorialCouncilAdherentMandate::create(
            $coTerrParis,
            $this->getReference('adherent-5'),
            new \DateTime('2020-02-02'),
            Genders::FEMALE,
            TerritorialCouncilQualityEnum::BOROUGH_COUNCILOR
        ));

        $manager->persist(TerritorialCouncilAdherentMandate::create(
            $coTerrParis,
            $this->getReference('adherent-19'),
            new \DateTime('2020-02-02'),
            Genders::FEMALE,
            TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR
        ));

        /** @var TerritorialCouncil $coTerr92 */
        $coTerr92 = $this->getReference('coTerr_92');

        $manager->persist(TerritorialCouncilAdherentMandate::create(
            $coTerr92,
            $this->getReference('adherent-2'),
            new \DateTime('2020-02-02'),
            Genders::FEMALE,
            TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT
        ));

        $manager->persist(TerritorialCouncilAdherentMandate::create(
            $coTerr92,
            $this->getReference('municipal-manager-1'),
            new \DateTime('2020-02-02'),
            Genders::FEMALE,
            TerritorialCouncilQualityEnum::CITY_COUNCILOR
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
