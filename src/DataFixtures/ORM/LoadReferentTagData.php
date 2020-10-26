<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Borough;
use App\Entity\Geo\CityCommunity;
use App\Entity\Geo\Country;
use App\Entity\Geo\CustomZone;
use App\Entity\Geo\Department;
use App\Entity\Geo\District;
use App\Entity\Geo\Region;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use App\Entity\ReferentTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @deprecated
 */
class LoadReferentTagData extends Fixture
{
    private const ZONE_TYPE_AS_TAG_TYPE = [
        Zone::CUSTOM => null,
        Zone::COUNTRY => ReferentTag::TYPE_COUNTRY,
        Zone::REGION => null,
        Zone::DEPARTMENT => ReferentTag::TYPE_DEPARTMENT,
        Zone::DISTRICT => ReferentTag::TYPE_DISTRICT,
        Zone::CITY => null,
        Zone::BOROUGH => ReferentTag::TYPE_BOROUGH,
        Zone::CITY_COMMUNITY => ReferentTag::TYPE_METROPOLIS,
        Zone::CANTON => null,
        Zone::FOREIGN_DISTRICT => ReferentTag::TYPE_DISTRICT,
        Zone::CONSULAR_DISTRICT => null,
    ];

    public function load(ObjectManager $manager): void
    {
        $this->loadFde($manager);
        $this->loadCountries($manager);
        $this->loadCorsica($manager);
        $this->loadDepartments($manager);
        $this->loadDistricts($manager);
        $this->loadLyon($manager);
        $this->loadBoroughs($manager);

        $manager->flush();
    }

    private function loadFde(ObjectManager $manager): void
    {
        /* @var CustomZone $fde */
        $fde = $this->getReference(LoadGeoData::FDE_REFERENCE);
        $referentTag = $this->createReferentTagFromZone($manager, $fde);
        $this->addReference('referent_tag_fde', $referentTag);
    }

    private function loadCountries(ObjectManager $manager): void
    {
        $countries = $manager->getRepository(Country::class)->findAll();
        foreach ($countries as $country) {
            $referentTag = $this->createReferentTagFromZone($manager, $country);
            $this->addReference('referent_tag_country_'.$country->getCode(), $referentTag);
        }
    }

    private function loadCorsica(ObjectManager $manager): void
    {
        /* @var Region $corsica */
        $corsica = $this->getReference(LoadGeoData::CORSICA_REFERENCE);
        $referentTag = $this->createReferentTagFromZone($manager, $corsica);
        $referentTag->setCode('corsica');
        $this->addReference('referent_tag_corsica', $referentTag);
    }

    private function loadDepartments(ObjectManager $manager): void
    {
        $departments = $manager->getRepository(Department::class)->findAll();
        foreach ($departments as $department) {
            $referentTag = $this->createReferentTagFromZone($manager, $department);
            $this->addReference('referent_tag_department_'.$department->getCode(), $referentTag);
        }
    }

    private function loadDistricts(ObjectManager $manager): void
    {
        $districts = $manager->getRepository(District::class)->findAll();
        foreach ($districts as $district) {
            $referentTag = $this->createReferentTagFromZone($manager, $district);
            $this->addReference('referent_tag_district_'.$district->getCode(), $referentTag);
        }
    }

    private function loadLyon(ObjectManager $manager): void
    {
        /* @var CityCommunity $lyon */
        $lyon = $this->getReference(LoadGeoData::LYON_REFERENCE);
        $referentTag = $this->createReferentTagFromZone($manager, $lyon);
        $this->addReference('referent_tag_lyon', $referentTag);
    }

    private function loadBoroughs(ObjectManager $manager): void
    {
        $boroughs = $manager->getRepository(Borough::class)->findAll();
        foreach ($boroughs as $borough) {
            $referentTag = $this->createReferentTagFromZone($manager, $borough);
            $this->addReference('referent_tag_borough_'.$referentTag->getCode(), $referentTag);
        }
    }

    private function createReferentTagFromZone(ObjectManager $manager, ZoneableInterface $geo): ReferentTag
    {
        $name = sprintf('%s (%s)', $geo->getName(), $geo->getCode());
        $referentTag = new ReferentTag($name, $geo->getCode());
        $referentTag->setType(self::ZONE_TYPE_AS_TAG_TYPE[$geo->getZoneType()]);

        $zone = $manager->getRepository(Zone::class)->zoneableAsZone($geo);
        $referentTag->setZone($zone);

        $manager->persist($zone);
        $manager->persist($referentTag);

        return $referentTag;
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoData::class,
        ];
    }
}
