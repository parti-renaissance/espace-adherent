<?php

namespace App\DataFixtures;

use AppBundle\Entity\ElectedRepresentative\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadZoneData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categoryCity1 = new Zone($this->getReference('zone-category-ville'), 'Clichy (92110)');
        $categoryCity2 = new Zone($this->getReference('zone-category-ville'), 'Lille (59000)');
        $categoryCity3 = new Zone($this->getReference('zone-category-ville'), 'Paris 7eme (75007)');
        $categoryCity4 = new Zone($this->getReference('zone-category-ville'), 'Nice (06000,06100,06200,06300)');
        $categoryCity5 = new Zone($this->getReference('zone-category-ville'), 'Lyon 1er (69001)');

        $manager->persist($categoryCity1);
        $manager->persist($categoryCity2);
        $manager->persist($categoryCity3);
        $manager->persist($categoryCity4);
        $manager->persist($categoryCity5);

        $categoryEpci1 = new Zone($this->getReference('zone-category-epci'), 'CA du Grand Guéret');
        $categoryEpci2 = new Zone($this->getReference('zone-category-epci'), 'Métropole Européenne de Lille');
        $categoryEpci3 = new Zone($this->getReference('zone-category-epci'), 'CU Grand Paris Seine et Oise');
        $categoryEpci4 = new Zone($this->getReference('zone-category-epci'), 'Métropole de LYON');
        $categoryEpci5 = new Zone($this->getReference('zone-category-epci'), 'CC du Cap Corse');

        $manager->persist($categoryEpci1);
        $manager->persist($categoryEpci2);
        $manager->persist($categoryEpci3);
        $manager->persist($categoryEpci4);
        $manager->persist($categoryEpci5);

        $categoryDepartment1 = new Zone($this->getReference('zone-category-département'), 'Ain (01)');
        $categoryDepartment2 = new Zone($this->getReference('zone-category-département'), 'Nord (59)');
        $categoryDepartment3 = new Zone($this->getReference('zone-category-département'), 'Paris (75)');
        $categoryDepartment4 = new Zone($this->getReference('zone-category-département'), 'Alpes-Maritimes (06)');
        $categoryDepartment5 = new Zone($this->getReference('zone-category-département'), 'Rhône (69)');
        $categoryDepartment6 = new Zone($this->getReference('zone-category-département'), 'Yvelines (78)');
        $categoryDepartment7 = new Zone($this->getReference('zone-category-département'), 'Corse-du-Sud (2A)');
        $categoryDepartment8 = new Zone($this->getReference('zone-category-département'), 'Haute-Corse (2B)');
        $categoryDepartment9 = new Zone($this->getReference('zone-category-département'), 'La Réunion (974)');

        $manager->persist($categoryDepartment1);
        $manager->persist($categoryDepartment2);
        $manager->persist($categoryDepartment3);
        $manager->persist($categoryDepartment4);
        $manager->persist($categoryDepartment5);
        $manager->persist($categoryDepartment6);
        $manager->persist($categoryDepartment7);
        $manager->persist($categoryDepartment8);
        $manager->persist($categoryDepartment9);

        $categoryRegion1 = new Zone($this->getReference('zone-category-région'), 'Hauts-de-France');
        $categoryRegion2 = new Zone($this->getReference('zone-category-région'), 'Île-de-France');
        $categoryRegion3 = new Zone($this->getReference('zone-category-région'), 'Provence-Alpes-Côte d\'Azur');
        $categoryRegion4 = new Zone($this->getReference('zone-category-région'), 'Corse');
        $categoryRegion5 = new Zone($this->getReference('zone-category-région'), 'La Réunion');

        $manager->persist($categoryRegion1);
        $manager->persist($categoryRegion2);
        $manager->persist($categoryRegion3);
        $manager->persist($categoryRegion4);
        $manager->persist($categoryRegion5);

        $categoryDistrict1 = new Zone($this->getReference('zone-category-circonscription'), 'Nord, 9ème circonscription (59-09)');
        $categoryDistrict2 = new Zone($this->getReference('zone-category-circonscription'), 'Paris, 7ème circonscription (75-07)');
        $categoryDistrict3 = new Zone($this->getReference('zone-category-circonscription'), 'Alpes-Maritimes, 3ème circonscription (06-03)');
        $categoryDistrict4 = new Zone($this->getReference('zone-category-circonscription'), 'Rhône, 10ème circonscription (69-10)');
        $categoryDistrict5 = new Zone($this->getReference('zone-category-circonscription'), 'Yvelines, 6ème circonscription (78-06)');
        $categoryDistrict6 = new Zone($this->getReference('zone-category-circonscription'), 'Corse-du-Sud, 1ère circonscription (2A-01)');
        $categoryDistrict7 = new Zone($this->getReference('zone-category-circonscription'), 'Réunion, 2ème circonscription (974-02)');

        $manager->persist($categoryDistrict1);
        $manager->persist($categoryDistrict2);
        $manager->persist($categoryDistrict3);
        $manager->persist($categoryDistrict4);
        $manager->persist($categoryDistrict5);
        $manager->persist($categoryDistrict6);
        $manager->persist($categoryDistrict7);

        $categoryCorsica = new Zone($this->getReference('zone-category-corse'), 'Corse');
        $manager->persist($categoryCorsica);

        $categoryFOF = new Zone($this->getReference('zone-category-fde'), 'Français de l\'Étranger');
        $manager->persist($categoryFOF);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadZoneCategoryData::class,
        ];
    }
}
