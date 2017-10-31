<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\NullablePostAddress;
use AppBundle\Group\GroupFactory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadGroupData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    const GROUP_1_UUID = 'aa364092-3999-4102-930c-f711ef971195';
    const GROUP_2_UUID = '552934ed-2ac6-4a3a-a490-ddc8bf959444';
    const GROUP_3_UUID = '942201fe-bffa-4fed-a551-71c3e49cea43';
    const GROUP_4_UUID = '31fe9de2-5ba2-4305-be82-8e9a329e2579';
    const GROUP_5_UUID = '0ac45a9f-8495-4b32-bd2d-e43a27f5e4b6';
    const GROUP_6_UUID = 'cff414ca-3ee7-43db-8201-0852b0c05334';
    const GROUP_7_UUID = 'fc83efde-17e5-4e87-b9e9-71b165aecd10';
    const GROUP_8_UUID = '55bc9c81-612b-4108-b5ae-d065a69456d1';
    const GROUP_9_UUID = 'eacefe0b-ace6-4ed5-a747-61f874f165f6';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        // Create some default groups and make people join them
        $groupFactory = $this->getGroupFactory();

        $group1 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_1_UUID,
            'created_by' => LoadAdherentData::ADHERENT_3_UUID,
            'created_at' => '2017-10-12 12:25:54',
            'name' => 'MOOC à Paris 8',
            'slug' => 'mooc-a-paris-8',
            'description' => 'L\'équipe MOOC des habitants du 8ème arrondissement de Paris.',
            'address' => NullablePostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.8705073, 2.3032432),
        ]);
        $group1->approved('2017-10-12 15:54:18');
        $this->addReference('group-1', $group1);

        $group2 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_2_UUID,
            'created_by' => LoadAdherentData::ADHERENT_6_UUID,
            'created_at' => '2017-10-12 18:34:12',
            'name' => 'MOOC à Marseille',
            'description' => "L'équipe MOOC à Marseille !",
            'address' => NullablePostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
            'phone' => '33 673643424',
        ]);
        $this->addReference('group-2', $group2);

        $group3 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_3_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-26 16:08:24',
            'name' => 'MOOC à Dammarie-les-Lys',
            'slug' => 'mooc-a-dammarie-les-lys',
            'description' => 'MOOC sans adresse et téléphone',
        ]);
        $group3->approved('2017-10-27 10:18:33');
        $this->addReference('group-3', $group3);

        $group4 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_4_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-09-19 07:36:55',
            'name' => 'Massive Open Online Course',
            'description' => 'Encore une équipe MOOC',
        ]);
        $group4->approved();
        $this->addReference('group-4', $group4);

        $group5 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_5_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-10-19 11:54:28',
            'name' => 'Formation en ligne ouverte à tous à Évry',
            'description' => 'Équipe de la formation en ligne ouverte à tous à Évry',
            'address' => NullablePostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', 48.6241569, 2.4265995),
            'phone' => '33 673654349',
        ]);
        $group5->approved();
        $this->addReference('group-5', $group5);

        $group6 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_6_UUID,
            'created_by' => LoadAdherentData::ADHERENT_9_UUID,
            'created_at' => '2017-09-18 20:12:33',
            'name' => 'Formation en ligne ouverte à tous',
            'description' => 'Équipe de la formation en ligne ouverte à tous',
            'phone' => '33 234823644',
        ]);
        $group6->approved('2017-10-19 09:17:24');
        $this->addReference('group-6', $group6);

        $group7 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_7_UUID,
            'created_by' => LoadAdherentData::ADHERENT_10_UUID,
            'created_at' => '2017-09-18 09:14:45',
            'name' => 'Équipe MOOC à Berlin',
            'description' => 'Équipe MOOC de nos partenaires Allemands.',
        ]);
        $group7->approved('2017-03-19 13:43:26');
        $this->addReference('group-7', $group7);

        $group8 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_8_UUID,
            'created_by' => LoadAdherentData::ADHERENT_11_UUID,
            'created_at' => '2017-10-10 17:34:18',
            'name' => 'En Marche - MOOC',
            'description' => 'Équipe MOOC.',
        ]);
        $group8->approved('2017-10-10 18:23:18');
        $this->addReference('group-8', $group8);

        $group9 = $groupFactory->createFromArray([
            'uuid' => self::GROUP_9_UUID,
            'created_by' => LoadAdherentData::ADHERENT_12_UUID,
            'created_at' => '2017-10-09 12:16:22',
            'name' => 'MOOC à New York City',
            'description' => 'Équipe MOOC à New York City.',
            'address' => NullablePostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', 40.7625289, -73.9859927),
            'phone' => '1 2123150100',
        ]);
        $group9->approved('2017-10-09 13:27:42');
        $this->addReference('group-9', $group9);

        $manager->persist($group1);
        $manager->persist($group2);
        $manager->persist($group3);
        $manager->persist($group4);
        $manager->persist($group5);
        $manager->persist($group6);
        $manager->persist($group7);
        $manager->persist($group8);
        $manager->persist($group9);

        // Make adherents join groups
        $manager->persist($this->getReference('adherent-3')->administrateGroup($group1, '2017-10-12 17:25:54'));
        $manager->persist($this->getReference('adherent-7')->administrateGroup($group3, '2017-10-26 17:08:24'));
        $manager->persist($this->getReference('adherent-7')->administrateGroup($group4));
        $manager->persist($this->getReference('adherent-7')->administrateGroup($group5));
        $manager->persist($this->getReference('adherent-2')->followGroup($group1));
        $manager->persist($this->getReference('adherent-4')->followGroup($group1));
        $manager->persist($this->getReference('adherent-5')->administrateGroup($group1));
        $manager->persist($this->getReference('adherent-6')->administrateGroup($group2));
        $manager->persist($this->getReference('adherent-3')->followGroup($group4));
        $manager->persist($this->getReference('adherent-3')->followGroup($group5));
        $manager->persist($this->getReference('adherent-9')->administrateGroup($group6));
        $manager->persist($this->getReference('adherent-3')->followGroup($group6));
        $manager->persist($this->getReference('adherent-10')->administrateGroup($group7));
        $manager->persist($this->getReference('adherent-3')->followGroup($group7));
        $manager->persist($this->getReference('adherent-3')->administrateGroup($group3));
        $manager->persist($this->getReference('adherent-9')->followGroup($group5));
        $manager->persist($this->getReference('adherent-11')->administrateGroup($group8));
        $manager->persist($this->getReference('adherent-3')->followGroup($group8));
        $manager->persist($this->getReference('adherent-12')->administrateGroup($group9));
        $manager->persist($this->getReference('adherent-3')->followGroup($group9));
        $manager->persist($this->getReference('adherent-11')->followGroup($group9));

        $manager->flush();
    }

    private function getGroupFactory(): GroupFactory
    {
        return $this->container->get('app.group.factory');
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
