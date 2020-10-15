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
use App\Entity\Geo\ForeignDistrict;
use App\Entity\Geo\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGeoData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $rows = $this->csvAsArray(__DIR__.'/../geo/00-custom.csv');
        foreach ($rows as $row) {
            $entity = new CustomZone($row['code'], $row['name']);

            $manager->persist($entity);
            $this->addReference(CustomZone::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/01-foreign-district.csv');
        foreach ($rows as $row) {
            /* @var CustomZone $customZone */
            $customZone = $this->getReference(CustomZone::class.'#'.$row['code_custom']);
            $entity = new ForeignDistrict($row['code'], $row['name'], $row['number'], $customZone);

            $manager->persist($entity);
            $this->addReference(ForeignDistrict::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/02-consular-district.csv');
        foreach ($rows as $row) {
            /* @var ForeignDistrict $foreignDistrict */
            $foreignDistrict = $this->getReference(ForeignDistrict::class.'#'.$row['code_foreign_district']);
            $entity = new ConsularDistrict($row['code'], $row['name'], $row['number'], $foreignDistrict);
            $entity->setCities(explode(',', $row['cities']));

            $manager->persist($entity);
            $this->addReference(ConsularDistrict::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/03-country.csv');
        foreach ($rows as $row) {
            $entity = new Country($row['code'], $row['name']);

            if ($row['code_foreign_district']) {
                /* @var ForeignDistrict $foreignDistrict */
                $foreignDistrict = $this->getReference(ForeignDistrict::class.'#'.$row['code_foreign_district']);
                $entity->setForeignDistrict($foreignDistrict);
            }

            $manager->persist($entity);
            $this->addReference(Country::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/04-region.csv');
        foreach ($rows as $row) {
            /* @var Country $country */
            $country = $this->getReference(Country::class.'#'.$row['code_country']);
            $entity = new Region($row['code'], $row['name'], $country);

            $manager->persist($entity);
            $this->addReference(Region::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/05-department.csv');
        foreach ($rows as $row) {
            /* @var Region $region */
            $region = $this->getReference(Region::class.'#'.$row['code_region']);
            $entity = new Department($row['code'], $row['name'], $region);

            $manager->persist($entity);
            $this->addReference(Department::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/06-canton.csv');
        foreach ($rows as $row) {
            /* @var Department $department */
            $department = $this->getReference(Department::class.'#'.$row['code_department']);
            $entity = new Canton($row['code'], $row['name'], $department);

            $manager->persist($entity);
            $this->addReference(Canton::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/07-city-community.csv');
        foreach ($rows as $row) {
            $entity = new CityCommunity($row['code'], $row['name']);

            foreach (array_filter(explode(',', $row['code_department'])) as $codeDepartment) {
                /* @var Department $department */
                $department = $this->getReference(Department::class.'#'.$codeDepartment);
                $entity->addDepartment($department);
            }

            $manager->persist($entity);
            $this->addReference(CityCommunity::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/08-city.csv');
        foreach ($rows as $row) {
            $entity = new City($row['code'], $row['name']);
            $entity->setPostalCode(array_filter(explode(',', $row['postal_code'])));
            $entity->setPopulation($row['population'] ?: null);

            /* @var Department $department */
            $department = $this->getReference(Department::class.'#'.$row['code_department']);
            $entity->setDepartment($department);

            if ($row['code_city_community']) {
                /* @var CityCommunity $cityCommunity */
                $cityCommunity = $this->getReference(CityCommunity::class.'#'.$row['code_city_community']);
                $entity->setCityCommunity($cityCommunity);
            }

            foreach (array_filter(explode(',', $row['code_canton'])) as $codeCanton) {
                /* @var Canton $canton */
                $canton = $this->getReference(Canton::class.'#'.$codeCanton);
                $entity->addCanton($canton);
            }

            $manager->persist($entity);
            $this->addReference(City::class.'#'.$entity->getCode(), $entity);
        }

        $rows = $this->csvAsArray(__DIR__.'/../geo/09-borough.csv');
        foreach ($rows as $row) {
            /* @var City $city */
            $city = $this->getReference(City::class.'#'.$row['code_city']);
            $entity = new Borough($row['code'], $row['name'], $city);
            $entity->setPostalCode(array_filter(explode(',', $row['postal_code'])));
            $entity->setPopulation($row['population'] ?: null);

            $manager->persist($entity);
            $this->addReference(Borough::class.'#'.$entity->getCode(), $entity);
        }

        $manager->flush();
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
