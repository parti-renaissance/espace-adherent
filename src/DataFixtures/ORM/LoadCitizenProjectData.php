<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\NullablePostAddress;
use AppBundle\CitizenProject\CitizenProjectFactory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCitizenProjectData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    const CITIZEN_PROJECT_1_UUID = 'aa364092-3999-4102-930c-f711ef971195';
    const CITIZEN_PROJECT_2_UUID = '552934ed-2ac6-4a3a-a490-ddc8bf959444';
    const CITIZEN_PROJECT_3_UUID = '942201fe-bffa-4fed-a551-71c3e49cea43';
    const CITIZEN_PROJECT_4_UUID = '31fe9de2-5ba2-4305-be82-8e9a329e2579';
    const CITIZEN_PROJECT_5_UUID = '0ac45a9f-8495-4b32-bd2d-e43a27f5e4b6';
    const CITIZEN_PROJECT_6_UUID = 'cff414ca-3ee7-43db-8201-0852b0c05334';
    const CITIZEN_PROJECT_7_UUID = 'fc83efde-17e5-4e87-b9e9-71b165aecd10';
    const CITIZEN_PROJECT_8_UUID = '55bc9c81-612b-4108-b5ae-d065a69456d1';
    const CITIZEN_PROJECT_9_UUID = 'eacefe0b-ace6-4ed5-a747-61f874f165f6';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        // Create some default citizen projects and make people join them
        $citizenProjectFactory = $this->getCitizenProjectFactory();

        $citizenProject1 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_1_UUID,
            'created_by' => LoadAdherentData::ADHERENT_3_UUID,
            'created_at' => '2017-10-12 12:25:54',
            'name' => 'Le projet citoyen à Paris 8',
            'slug' => 'projet-citoyen-a-paris-8',
            'description' => 'Le projet citoyen des habitants du 8ème arrondissement de Paris.',
            'address' => NullablePostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.8705073, 2.3032432),
        ]);
        $citizenProject1->approved('2017-10-12 15:54:18');
        $this->addReference('citizen-project-1', $citizenProject1);

        $citizenProject2 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_2_UUID,
            'created_by' => LoadAdherentData::ADHERENT_6_UUID,
            'created_at' => '2017-10-12 18:34:12',
            'name' => 'Le projet citoyen à Marseille',
            'description' => 'Le projet citoyen à Marseille !',
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
            'phone' => '33 673643424',
        ]);
        $this->addReference('citizen-project-2', $citizenProject2);

        $citizenProject3 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_3_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-26 16:08:24',
            'name' => 'Le projet citoyen à Dammarie-les-Lys',
            'slug' => 'projet-citoyen-a-dammarie-les-lys',
            'description' => 'Le projet citoyen sans adresse et téléphone',
        ]);
        $citizenProject3->approved('2017-10-27 10:18:33');
        $this->addReference('citizen-project-3', $citizenProject3);

        $citizenProject4 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_4_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-09-19 07:36:55',
            'name' => 'Massive Open Online Course',
            'description' => 'Encore un projet citoyen',
        ]);
        $citizenProject4->approved();
        $this->addReference('citizen-project-4', $citizenProject4);

        $citizenProject5 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_5_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-19 11:54:28',
            'name' => 'Formation en ligne ouverte à tous à Évry',
            'description' => 'Équipe de la formation en ligne ouverte à tous à Évry',
            'address' => NullablePostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', 48.6241569, 2.4265995),
            'phone' => '33 673654349',
        ]);
        $citizenProject5->approved();
        $this->addReference('citizen-project-5', $citizenProject5);

        $citizenProject6 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_6_UUID,
            'created_by' => LoadAdherentData::ADHERENT_9_UUID,
            'created_at' => '2017-09-18 20:12:33',
            'name' => 'Formation en ligne ouverte à tous',
            'description' => 'Équipe de la formation en ligne ouverte à tous',
            'phone' => '33 234823644',
        ]);
        $citizenProject6->approved('2017-10-19 09:17:24');
        $this->addReference('citizen-project-6', $citizenProject6);

        $citizenProject7 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_7_UUID,
            'created_by' => LoadAdherentData::ADHERENT_10_UUID,
            'created_at' => '2017-09-18 09:14:45',
            'name' => 'Projet citoyen à Berlin',
            'description' => 'Projet citoyen de nos partenaires Allemands.',
        ]);
        $citizenProject7->approved('2017-03-19 13:43:26');
        $this->addReference('citizen-project-7', $citizenProject7);

        $citizenProject8 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_8_UUID,
            'created_by' => LoadAdherentData::ADHERENT_11_UUID,
            'created_at' => '2017-10-10 17:34:18',
            'name' => 'En Marche - Projet citoyen',
            'description' => 'Projet citoyen.',
        ]);
        $citizenProject8->approved('2017-10-10 18:23:18');
        $this->addReference('citizen-project-8', $citizenProject8);

        $citizenProject9 = $citizenProjectFactory->createFromArray([
            'uuid' => self::CITIZEN_PROJECT_9_UUID,
            'created_by' => LoadAdherentData::ADHERENT_12_UUID,
            'created_at' => '2017-10-09 12:16:22',
            'name' => 'Projet citoyen à New York City',
            'description' => 'Projet citoyen à New York City.',
            'address' => NullablePostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 40.7625289, -73.9859927),
            'phone' => '1 2123150100',
        ]);
        $citizenProject9->approved('2017-10-09 13:27:42');
        $this->addReference('citizen-project-9', $citizenProject9);

        $manager->persist($citizenProject1);
        $manager->persist($citizenProject2);
        $manager->persist($citizenProject3);
        $manager->persist($citizenProject4);
        $manager->persist($citizenProject5);
        $manager->persist($citizenProject6);
        $manager->persist($citizenProject7);
        $manager->persist($citizenProject8);
        $manager->persist($citizenProject9);

        // Make adherents join citizen projects
        $manager->persist($this->getReference('adherent-3')->administrateCitizenProject($citizenProject1, '2017-10-12 17:25:54'));
        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject3, '2017-10-26 17:08:24'));
        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject4));
        $manager->persist($this->getReference('adherent-7')->administrateCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-2')->followCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-4')->followCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-5')->administrateCitizenProject($citizenProject1));
        $manager->persist($this->getReference('adherent-6')->administrateCitizenProject($citizenProject2));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject4));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-9')->administrateCitizenProject($citizenProject6));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject6));
        $manager->persist($this->getReference('adherent-10')->administrateCitizenProject($citizenProject7));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject7));
        $manager->persist($this->getReference('adherent-3')->administrateCitizenProject($citizenProject3));
        $manager->persist($this->getReference('adherent-9')->followCitizenProject($citizenProject5));
        $manager->persist($this->getReference('adherent-11')->administrateCitizenProject($citizenProject8));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject8));
        $manager->persist($this->getReference('adherent-12')->administrateCitizenProject($citizenProject9));
        $manager->persist($this->getReference('adherent-3')->followCitizenProject($citizenProject9));
        $manager->persist($this->getReference('adherent-11')->followCitizenProject($citizenProject9));

        $manager->flush();
    }

    private function getCitizenProjectFactory(): CitizenProjectFactory
    {
        return $this->container->get('app.citizen_project.factory');
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
