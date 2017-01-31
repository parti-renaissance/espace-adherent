<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Committee\CommitteeFactory;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Entity\PostAddress;
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
    const ADHERENT_7_UUID = 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4';

    const COMMITTEE_1_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';
    const COMMITTEE_2_UUID = '182d8586-8b05-4b70-a727-704fa701e816';
    const COMMITTEE_3_UUID = 'b0cd0e52-a5a4-410b-bba3-37afdd326a0a';
    const COMMITTEE_4_UUID = 'd648d486-fbb3-4394-b4b3-016fac3658af';
    const COMMITTEE_5_UUID = '464d4c23-cf4c-4d3a-8674-a43910da6419';

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
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', 47.3950786, 8.5361402),
            'birthdate' => '1972-11-23',
        ]);

        $adherent2 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_2_UUID,
            'password' => 'secret!12345',
            'email' => 'carl999@example.fr',
            'gender' => 'male',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', 45.570898, 5.927206),
            'birthdate' => '1950-07-08',
            'position' => 'retired',
            'phone' => '33 0111223344',
            'registered_at' => '2016-11-16 20:45:33',
        ]);
        $adherent2->disableCommitteesNotifications();

        $adherent3 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_3_UUID,
            'password' => 'changeme1337',
            'email' => 'jacques.picard@en-marche.fr',
            'gender' => 'male',
            'first_name' => 'Jacques',
            'last_name' => 'Picard',
            'address' => PostAddress::createFrenchAddress('36 rue de la Paix', '75008-75108', 48.8699464, 2.3297187),
            'birthdate' => '1953-04-03',
            'position' => 'retired',
            'phone' => '33 187264236',
            'registered_at' => '2017-01-03 08:47:54',
        ]);
        $adherent3->enableCommitteesNotifications();

        $adherent4 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_4_UUID,
            'password' => 'EnMarche2017',
            'email' => 'luciole1989@spambox.fr',
            'gender' => 'female',
            'first_name' => 'Lucie',
            'last_name' => 'Olivera',
            'address' => PostAddress::createFrenchAddress('13 boulevard des Italiens', '75009-75109', 48.8713224, 2.3353755),
            'birthdate' => '1989-09-17',
            'position' => 'student',
            'phone' => '33 727363643',
            'registered_at' => '2017-01-18 13:15:28',
        ]);
        $adherent4->enableCommitteesNotifications();

        $adherent5 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_5_UUID,
            'password' => 'ILoveYouManu',
            'email' => 'gisele-berthoux@caramail.com',
            'gender' => 'female',
            'first_name' => 'Gisele',
            'last_name' => 'Berthoux',
            'address' => PostAddress::createFrenchAddress('47 rue Martre', '92110-92024', 48.9015986, 2.3052684),
            'birthdate' => '1983-12-24',
            'position' => 'unemployed',
            'phone' => '33 138764334',
            'registered_at' => '2017-01-08 05:55:43',
        ]);
        $adherent5->enableCommitteesNotifications();

        $adherent6 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_6_UUID,
            'password' => 'HipHipHip',
            'email' => 'benjyd@aol.com',
            'gender' => 'male',
            'first_name' => 'Benjamin',
            'last_name' => 'Duroc',
            'address' => PostAddress::createFrenchAddress('39 rue de Crimée', '13003-13203', 43.3062866, 5.3791498),
            'birthdate' => '1987-02-08',
            'position' => 'employed',
            'phone' => '33 673643424',
            'registered_at' => '2017-01-16 18:33:22',
        ]);
        $adherent6->enableCommitteesNotifications();

        $adherent7 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_7_UUID,
            'password' => 'Champion20',
            'email' => 'francis.brioul@yahoo.com',
            'gender' => 'male',
            'first_name' => 'Francis',
            'last_name' => 'Brioul',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', 48.5278939, 2.6484923),
            'birthdate' => '1962-01-07',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent7->enableCommitteesNotifications();

        // Create adherents accounts activation keys
        $key1 = AdherentActivationToken::generate($adherent1);
        $key2 = AdherentActivationToken::generate($adherent2);
        $key3 = AdherentActivationToken::generate($adherent3);
        $key4 = AdherentActivationToken::generate($adherent4);
        $key5 = AdherentActivationToken::generate($adherent5);
        $key6 = AdherentActivationToken::generate($adherent6);
        $key7 = AdherentActivationToken::generate($adherent7);

        // Enable some adherents accounts
        $adherent2->activate($key2, '2016-11-16 20:54:13');
        $adherent3->activate($key3, '2017-01-03 09:12:37');
        $adherent4->activate($key4, '2017-01-18 13:23:50');
        $adherent5->activate($key5, '2017-01-08 06:42:56');
        $adherent6->activate($key6, '2017-01-17 08:07:45');
        $adherent7->activate($key7, '2017-01-25 19:34:02');

        // Create some default committees and make people join them
        $committeeFactory = $this->getCommitteeFactory();

        $committee1 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_1_UUID,
            'created_by' => (string) $adherent3->getUuid(),
            'created_at' => '2017-01-12 13:25:54',
            'name' => 'En Marche Paris 8',
            'slug' => 'en-marche-paris-8',
            'description' => 'Le comité « En Marche ! » des habitants du 8ème arrondissement de Paris.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', 48.8705073, 2.3032432),
            'facebook_page_url' => 'https://facebook.com/enmarche-paris-8',
            'twitter_nickname' => 'enmarche75008',
        ]);
        $committee1->approved();

        $committee2 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_2_UUID,
            'created_by' => (string) $adherent6->getUuid(),
            'created_at' => '2017-01-12 19:34:12',
            'name' => 'En Marche Marseille 3',
            'description' => "En Marche ! C'est aussi à Marseille !",
            'address' => PostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
        ]);

        $committee3 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_3_UUID,
            'created_by' => (string) $adherent7->getUuid(),
            'created_at' => '2017-01-15 16:08:24',
            'name' => 'En Marche Dammarie-les-Lys',
            'slug' => 'en-marche-dammarie-les-lys',
            'description' => 'Les jeunes avec En Marche !',
            'address' => PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', 48.5182194, 2.6220158),
        ]);
        $committee3->approved();

        $committee4 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_4_UUID,
            'created_by' => (string) $adherent7->getUuid(),
            'created_at' => '2017-01-19 08:36:55',
            'name' => 'Antenne En Marche de Fontainebleau',
            'description' => 'Vous êtes Bellifontain ? Nous aussi ! Rejoignez-nous !',
            'address' => PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', 48.4047652, 2.6987591),
        ]);
        $committee4->approved();

        $committee5 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_5_UUID,
            'created_by' => (string) $adherent7->getUuid(),
            'created_at' => '2017-01-19 10:54:28',
            'name' => 'En Marche - Comité de Évry',
            'description' => 'En Marche pour une nouvelle vision, du renouveau pour la France.',
            'address' => PostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', 48.6241569, 2.4265995),
        ]);
        $committee5->approved();

        // Make an adherent request a new password
        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent1);

        // °\_O_/° Persist all the things (in memory) !!!
        $manager->persist($adherent1);
        $manager->persist($adherent2);
        $manager->persist($adherent3);
        $manager->persist($adherent4);
        $manager->persist($adherent5);
        $manager->persist($adherent6);
        $manager->persist($adherent7);

        $manager->persist($key1);
        $manager->persist($key2);
        $manager->persist($key3);
        $manager->persist($key4);
        $manager->persist($key5);
        $manager->persist($key6);

        $manager->persist($resetPasswordToken);

        $manager->persist($committee1);
        $manager->persist($committee2);
        $manager->persist($committee3);
        $manager->persist($committee4);
        $manager->persist($committee5);

        // Make adherents join committees
        $manager->persist($adherent3->hostCommittee($committee1));
        $manager->persist($adherent7->hostCommittee($committee3));
        $manager->persist($adherent7->hostCommittee($committee4));
        $manager->persist($adherent7->hostCommittee($committee5));
        $manager->persist($adherent2->followCommittee($committee1));
        $manager->persist($adherent4->followCommittee($committee1));
        $manager->persist($adherent5->hostCommittee($committee1));
        $manager->persist($adherent6->hostCommittee($committee2));

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
