<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadTerritorialCouncilMembershipData extends Fixture
{
    public const MEMBERSHIP_UUID1 = 'ad3780fe-d607-4d01-bc1a-d537fe351908';

    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-3'), new \DateTime('2020-06-06'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, 'Super comité de Paris'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, '75'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-4'), new \DateTime('2020-07-07'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::LRE_MANAGER, 'Paris 75'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-5'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, '75'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CONSULAR_CONSELOR, '75'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, '75010'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-8'), new \DateTime('2020-03-03'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::REFERENT, '75'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::MAYOR, '75012'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-19'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Paris en marche!'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CONSULAR_CONSELOR, '75009'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, '75'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('deputy-75-1'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DEPUTY, 'CIRCO 75010'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::MAYOR, '75010'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, '75010'));
        $manager->persist($membership);

        $membership = new TerritorialCouncilMembership($coTerrParis, $this->getReference('adherent-12'), new \DateTime('2020-02-02'), Uuid::fromString(self::MEMBERSHIP_UUID1));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::MAYOR, '75011'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, '75011'));
        $manager->persist($membership);

        /** @var TerritorialCouncil $coTerr92 */
        $coTerr92 = $this->getReference('coTerr_92');

        $membership = new TerritorialCouncilMembership($coTerr92, $this->getReference('adherent-2'), new \DateTime('2020-02-02'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, 'Comité de 92 en marche!'));
        $membership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::CITY_COUNCILOR, 'Chatillon'));
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
