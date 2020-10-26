<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Borough;
use App\Entity\Geo\Canton;
use App\Entity\Geo\City;
use App\Entity\Geo\CityCommunity;
use App\Entity\Geo\ConsularDistrict;
use App\Entity\Geo\Country;
use App\Entity\Geo\CustomZone;
use App\Entity\Geo\Department;
use App\Entity\Geo\District;
use App\Entity\Geo\ForeignDistrict;
use App\Entity\Geo\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGeoData extends Fixture
{
    public const FDE_REFERENCE = 'geo_custom_FDE';
    public const CORSICA_REFERENCE = 'geo_region_94';
    public const LYON_REFERENCE = 'geo_city_community_200046977';

    public function load(ObjectManager $manager): void
    {
        $this->loadCustoms($manager);
        $this->loadForeignDistricts($manager);
        $this->loadConsularDistricts($manager);
        $this->loadCountries($manager);
        $this->loadRegions($manager);
        $this->loadDepartments($manager);
        $this->loadDistricts($manager);
        $this->loadCantons($manager);
        $this->loadCityCommunities($manager);
        $this->loadCities($manager);
        $this->loadBoroughs($manager);

        $manager->flush();
    }

    private function loadCustoms(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/00-custom.csv');
        foreach ($rows as $row) {
            $entity = new CustomZone($row['code'], $row['name']);

            $manager->persist($entity);
            $this->addReference('geo_custom_'.$entity->getCode(), $entity);
        }
    }

    private function loadForeignDistricts(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/01-foreign-district.csv');
        foreach ($rows as $row) {
            /* @var CustomZone $customZone */
            $customZone = $this->getReference('geo_custom_'.$row['code_custom']);
            $entity = new ForeignDistrict($row['code'], $row['name'], $row['number'], $customZone);

            $manager->persist($entity);
            $this->addReference('geo_foreign_district_'.$entity->getCode(), $entity);
        }
    }

    private function loadConsularDistricts(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/02-consular-district.csv');
        foreach ($rows as $row) {
            /* @var ForeignDistrict $foreignDistrict */
            $foreignDistrict = $this->getReference('geo_foreign_district_'.$row['code_foreign_district']);
            $entity = new ConsularDistrict($row['code'], $row['name'], $row['number'], $foreignDistrict);
            $entity->setCities(explode(',', $row['cities']));

            $manager->persist($entity);
            $this->addReference('geo_consular_district_'.$entity->getCode(), $entity);
        }
    }

    private function loadCountries(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/03-country.csv');
        foreach ($rows as $row) {
            $entity = new Country($row['code'], $row['name']);

            if ($row['code_foreign_district']) {
                /* @var ForeignDistrict $foreignDistrict */
                $foreignDistrict = $this->getReference('geo_foreign_district_'.$row['code_foreign_district']);
                $entity->setForeignDistrict($foreignDistrict);
            }

            $manager->persist($entity);
            $this->addReference('geo_country_'.$entity->getCode(), $entity);
        }
    }

    private function loadRegions(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/04-region.csv');
        foreach ($rows as $row) {
            /* @var Country $country */
            $country = $this->getReference('geo_country_'.$row['code_country']);
            $entity = new Region($row['code'], $row['name'], $country);

            $manager->persist($entity);
            $this->addReference('geo_region_'.$entity->getCode(), $entity);
        }
    }

    private function loadDepartments(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/05-department.csv');
        foreach ($rows as $row) {
            /* @var Region $region */
            $region = $this->getReference('geo_region_'.$row['code_region']);
            $entity = new Department($row['code'], $row['name'], $region);

            $manager->persist($entity);
            $this->addReference('geo_department_'.$entity->getCode(), $entity);
        }
    }

    private function loadDistricts(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/06-districts.csv');
        foreach ($rows as $row) {
            /* @var Department $department */
            $department = $this->getReference('geo_department_'.$row['code_department']);
            $entity = new District($row['code'], $row['name'], $row['number'], $department);

            $manager->persist($entity);
            $this->addReference('geo_district_'.$entity->getCode(), $entity);
        }
    }

    private function loadCantons(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/07-canton.csv');
        foreach ($rows as $row) {
            /* @var Department $department */
            $department = $this->getReference('geo_department_'.$row['code_department']);
            $entity = new Canton($row['code'], $row['name'], $department);

            $manager->persist($entity);
            $this->addReference('geo_canton_'.$entity->getCode(), $entity);
        }
    }

    private function loadCityCommunities(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/08-city-community.csv');
        foreach ($rows as $row) {
            $entity = new CityCommunity($row['code'], $row['name']);

            foreach (array_filter(explode(',', $row['code_department'])) as $codeDepartment) {
                /* @var Department $department */
                $department = $this->getReference('geo_department_'.$codeDepartment);
                $entity->addDepartment($department);
            }

            $manager->persist($entity);
            $this->addReference('geo_city_community_'.$entity->getCode(), $entity);
        }
    }

    private function loadCities(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/09-city.csv');
        foreach ($rows as $row) {
            $entity = new City($row['code'], $row['name']);
            $entity->setPostalCode(array_filter(explode(',', $row['postal_code'])));
            $entity->setPopulation($row['population'] ?: null);

            /* @var Department $department */
            $department = $this->getReference('geo_department_'.$row['code_department']);
            $entity->setDepartment($department);

            if ($row['code_city_community']) {
                /* @var CityCommunity $cityCommunity */
                $cityCommunity = $this->getReference('geo_city_community_'.$row['code_city_community']);
                $entity->setCityCommunity($cityCommunity);
            }

            foreach (array_filter(explode(',', $row['code_canton'])) as $codeCanton) {
                /* @var Canton $canton */
                $canton = $this->getReference('geo_canton_'.$codeCanton);
                $entity->addCanton($canton);
            }

            $manager->persist($entity);
            $this->addReference('geo_city_'.$entity->getCode(), $entity);
        }
    }

    private function loadBoroughs(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/10-borough.csv');
        foreach ($rows as $row) {
            /* @var City $city */
            $city = $this->getReference('geo_city_'.$row['code_city']);
            $entity = new Borough($row['code'], $row['name'], $city);
            $entity->setPostalCode(array_filter(explode(',', $row['postal_code'])));
            $entity->setPopulation($row['population'] ?: null);

            $manager->persist($entity);
            $this->addReference('geo_borough_'.$entity->getCode(), $entity);
        }
    }

    private function csvAsArray(string $filename): iterable
    {
        $handle = fopen($filename, 'rb');
        $header = fgetcsv($handle);
        while ($raw = fgetcsv($handle)) {
            yield array_combine($header, $raw);
        }
        fclose($handle);
    }
}
