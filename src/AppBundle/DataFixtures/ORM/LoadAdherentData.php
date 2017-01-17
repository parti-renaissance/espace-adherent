<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Committee\CommitteeFactory;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Membership\AdherentFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadAdherentData implements FixtureInterface, ContainerAwareInterface
{
    const ADHERENT_1_UUID = '313bd28f-efc8-57c9-8ab7-2106c8be9697';
    const ADHERENT_2_UUID = 'e6977a4d-2646-5f6c-9c82-88e58dca8458';
    const ADHERENT_3_UUID = 'a046adbe-9c7b-56a9-a676-6151a6785dda';
    const ADHERENT_4_UUID = '29461c49-6316-5be1-9ac3-17816bf2d819';
    const ADHERENT_5_UUID = 'b4219d47-3138-5efd-9762-2ef9f9495084';
    const ADHERENT_6_UUID = 'acc73b03-9743-47d8-99db-5a6c6f55ad67';

    const COMMITTEE_1_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';
    const COMMITTEE_2_UUID = '182d8586-8b05-4b70-a727-704fa701e816';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $adherentFactory = $this->getAdherentFactory();

        // Create adherent users list
        $adherent1 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_1_UUID,
            'password' => 'secret!12345',
            'email' => 'michelle.dufour@example.ch',
            'gender' => 'female',
            'first_name' => 'Michelle',
            'last_name' => 'Dufour',
            'country' => 'CH',
            'birthdate' => '1972-11-23',
        ]);

        $adherent2 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_2_UUID,
            'password' => 'secret!12345',
            'email' => 'carl999@example.fr',
            'gender' => 'male',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'country' => 'FR',
            'address' => '122 rue de Mouxy',
            'city' => '73100-73182',
            'postal_code' => '73100',
            'birthdate' => '1950-07-08',
            'position' => 'retired',
            'phone' => '33 0111223344',
            'registered_at' => '2016-11-16 20:45:33',
        ]);

        $adherent3 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_3_UUID,
            'password' => 'changeme1337',
            'email' => 'jacques.picard@en-marche.fr',
            'gender' => 'male',
            'first_name' => 'Jacques',
            'last_name' => 'Picard',
            'country' => 'FR',
            'address' => '36 rue de la Paix',
            'city' => '75008-75108',
            'postal_code' => '75008',
            'birthdate' => '1953-04-03',
            'position' => 'retired',
            'phone' => '33 187264236',
            'registered_at' => '2017-01-03 08:47:54',
        ]);

        $adherent4 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_4_UUID,
            'password' => 'EnMarche2017',
            'email' => 'luciole1989@spambox.fr',
            'gender' => 'female',
            'first_name' => 'Lucie',
            'last_name' => 'Olivera',
            'country' => 'FR',
            'address' => '13 boulevard des Italiens',
            'city' => '75009-75109',
            'postal_code' => '75009',
            'birthdate' => '1989-09-17',
            'position' => 'student',
            'phone' => '33 727363643',
            'registered_at' => '2017-01-18 13:15:28',
        ]);

        $adherent5 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_5_UUID,
            'password' => 'ILoveYouManu',
            'email' => 'gisele-berthoux@caramail.com',
            'gender' => 'female',
            'first_name' => 'Gisele',
            'last_name' => 'Berthoux',
            'country' => 'FR',
            'address' => '47 rue Martre',
            'city' => '92110-92024',
            'postal_code' => '92110',
            'birthdate' => '1983-12-24',
            'position' => 'unemployed',
            'phone' => '33 138764334',
            'registered_at' => '2017-01-08 05:55:43',
        ]);

        $adherent6 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_6_UUID,
            'password' => 'HipHipHip',
            'email' => 'benjyd@aol.com',
            'gender' => 'male',
            'first_name' => 'Benjamin',
            'last_name' => 'Duroc',
            'country' => 'FR',
            'address' => '39 rue de Crimée',
            'city' => '13003-13203',
            'postal_code' => '13003',
            'birthdate' => '1987-02-08',
            'position' => 'employed',
            'phone' => '33 673643424',
            'registered_at' => '2017-01-16 18:33:22',
        ]);

        // Create adherents accounts activation keys
        $key1 = AdherentActivationToken::generate($adherent1);
        $key2 = AdherentActivationToken::generate($adherent2);
        $key3 = AdherentActivationToken::generate($adherent3);
        $key4 = AdherentActivationToken::generate($adherent4);
        $key5 = AdherentActivationToken::generate($adherent5);
        $key6 = AdherentActivationToken::generate($adherent6);

        // Enable some adherents accounts
        $adherent2->activate($key2, '2016-11-16 20:54:13');
        $adherent3->activate($key3, '2017-01-03 09:12:37');
        $adherent4->activate($key4, '2017-01-18 13:23:50');
        $adherent5->activate($key5, '2017-01-08 06:42:56');
        $adherent6->activate($key6, '2017-01-17 08:07:45');

        // Create some default committees and make people join them
        $committeeFactory = $this->getCommitteeFactory();

        $committee1 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_1_UUID,
            'created_by' => (string) $adherent3->getUuid(),
            'name' => 'En Marche Paris 8',
            'description' => 'Le comité « En Marche ! » des habitants du 8ème arrondissement de Paris.',
            'country' => 'FR',
            'postal_code' => '75008',
            'city_code' => '75008-75108',
            'facebook_page_url' => 'https://facebook.com/enmarche-paris-8',
            'twitter_nickname' => 'enmarche75008',
        ]);

        $committee2 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_2_UUID,
            'created_by' => (string) $adherent6->getUuid(),
            'name' => 'En Marche Marseille 3',
            'description' => "En Marche ! C'est aussi à Marseille !",
            'country' => 'FR',
            'postal_code' => '13003',
            'city_code' => '13003-13203',
        ]);

        // Make an adherent request a new password
        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent1);

        // °\_O_/° Persist all the things (in memory) !!!
        $manager->persist($adherent1);
        $manager->persist($adherent2);
        $manager->persist($adherent3);
        $manager->persist($adherent4);
        $manager->persist($adherent5);
        $manager->persist($adherent6);

        $manager->persist($key1);
        $manager->persist($key2);
        $manager->persist($key3);
        $manager->persist($key4);
        $manager->persist($key5);
        $manager->persist($key6);

        $manager->persist($resetPasswordToken);

        $manager->persist($committee1);
        $manager->persist($committee2);

        // Make adherents join committees
        $manager->persist($committee1->approved('2017-01-03 15:18:22'));
        $manager->persist($adherent2->followCommittee($committee1));
        $manager->persist($adherent4->followCommittee($committee1));
        $manager->persist($adherent5->hostCommittee($committee1));

        $manager->flush();
    }

    private function getAdherentFactory(): AdherentFactory
    {
        return $this->container->get('app.membership.adherent_factory');
    }

    private function getCommitteeFactory(): CommitteeFactory
    {
        return $this->container->get('app.committee_factory');
    }
}
