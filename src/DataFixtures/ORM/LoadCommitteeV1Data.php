<?php

namespace App\DataFixtures\ORM;

use App\Committee\CommitteeFactory;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\NullablePostAddress;
use App\Entity\VotingPlatform\Designation\Designation;
use App\FranceCities\FranceCities;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommitteeV1Data extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const COMMITTEE_1_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';
    public const COMMITTEE_2_UUID = '182d8586-8b05-4b70-a727-704fa701e816';
    public const COMMITTEE_3_UUID = 'b0cd0e52-a5a4-410b-bba3-37afdd326a0a';
    public const COMMITTEE_4_UUID = 'd648d486-fbb3-4394-b4b3-016fac3658af';
    public const COMMITTEE_5_UUID = '464d4c23-cf4c-4d3a-8674-a43910da6419';
    public const COMMITTEE_6_UUID = '508d4ac0-27d6-4635-8953-4cc8600018f9';
    public const COMMITTEE_7_UUID = '40b6e2e5-2499-438b-93ab-ef08860a1845';
    public const COMMITTEE_8_UUID = '93b72179-7d27-40c4-948c-5188aaf264b6';
    public const COMMITTEE_9_UUID = '62ea97e7-6662-427b-b90a-23429136d0dd';
    public const COMMITTEE_10_UUID = '79638242-5101-11e7-b114-b2f933d5fe66';
    public const COMMITTEE_11_UUID = 'eb050b5e-9444-49ec-b3dc-005c98024507';
    public const COMMITTEE_12_UUID = '138140a5-1dd2-11b2-88c6-671b351502ee';
    public const COMMITTEE_13_UUID = '1381405c-1dd2-11b2-9a2f-bc94782bb639';
    public const COMMITTEE_14_UUID = 'bb256335-aa42-134a-8fba-525d3ea32b7d';
    public const COMMITTEE_15_UUID = '13814081-1dd2-11b2-abfc-9a31f72792e5';
    public const COMMITTEE_16_UUID = '9640c5fc-c904-428f-8a79-2d90e555478a';

    private $committeeFactory;

    public function __construct(CommitteeFactory $committeeFactory, FranceCities $franceCities)
    {
        parent::__construct($franceCities);

        $this->committeeFactory = $committeeFactory;
    }

    public function load(ObjectManager $manager): void
    {
        // Create some default committees and make people join them
        $committee1 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_1_UUID,
            'created_by' => LoadAdherentData::ADHERENT_3_UUID,
            'created_at' => '2017-01-12 13:25:54',
            'name' => 'En Marche Paris 8',
            'slug' => 'en-marche-paris-8',
            'description' => 'Le comité « En Marche ! » des habitants du 8ème arrondissement de Paris.',
            'address' => $this->createNullablePostAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.8705073, 2.3132432),
            'phone' => '+33187264236',
            'facebook_page_url' => 'https://facebook.com/enmarche-paris-8',
            'twitter_nickname' => 'enmarche75008',
        ]);
        $committee1->approved('2017-01-12 15:54:18');
        $committee1->addElection(new CommitteeElection($this->getReference('designation-3', Designation::class)));
        $committee1->addElection(new CommitteeElection($this->getReference('designation-4', Designation::class)));
        $this->addReference('committee-1', $committee1);

        $committee2 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_2_UUID,
            'created_by' => LoadAdherentData::ADHERENT_6_UUID,
            'created_at' => '2017-01-12 19:34:12',
            'name' => 'En Marche Marseille 3',
            'description' => "En Marche ! C'est aussi à Marseille !",
            'address' => $this->createNullablePostAddress('30 Boulevard Louis Guichoux', '13003-13203', null, 43.3256095, 5.374416),
            'phone' => '+33673643424',
            'status' => Committee::PENDING,
        ]);
        $this->addReference('committee-2', $committee2);

        $committee3 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_3_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-01-26 16:08:24',
            'name' => 'En Marche Dammarie-les-Lys',
            'slug' => 'en-marche-dammarie-les-lys',
            'description' => 'Les jeunes avec En Marche !',
            'address' => $this->createNullablePostAddress('826 Avenue du Lys', '77190-77152', null, 48.5182194, 2.6220158),
            'phone' => '+33673654349',
            'name_locked' => true,
        ]);
        $committee3->approved('2017-01-27 09:18:33');
        $committee3->addElection(new CommitteeElection($this->getReference('designation-3', Designation::class)));
        $this->addReference('committee-3', $committee3);

        $committee4 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_4_UUID,
            'created_by' => LoadAdherentData::ADHERENT_4_UUID,
            'created_at' => '2017-01-19 08:36:55',
            'name' => 'Antenne En Marche de Fontainebleau',
            'description' => 'Vous êtes Bellifontain ? Nous aussi ! Rejoignez-nous !',
            'address' => $this->createNullablePostAddress('40 Rue Grande', '77300-77186', null, 48.4047652, 2.6987591),
            'phone' => '+33673654349',
        ]);
        $committee4->approved('-35 days');
        $committee4->setCurrentElection(new CommitteeElection($this->getReference('designation-3', Designation::class)));
        $this->addReference('committee-4', $committee4);

        $committee5 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_5_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-01-19 10:54:28',
            'name' => 'En Marche - Comité de Évry',
            'description' => 'En Marche pour une nouvelle vision, du renouveau pour la France.',
            'address' => $this->createNullablePostAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', null, 48.6241569, 2.4265995),
            'phone' => '+33673654349',
        ]);
        $committee5->approved();
        $committee5->setCurrentElection(new CommitteeElection($this->getReference('designation-2', Designation::class)));
        $this->addReference('committee-5', $committee5);

        $committee6 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_6_UUID,
            'created_by' => LoadAdherentData::ADHERENT_9_UUID,
            'created_at' => '2017-03-18 20:12:33',
            'name' => 'En Marche - Comité de Rouen',
            'description' => 'En Marche pour la France et la ville de Rouen.',
            'address' => $this->createNullablePostAddress('2 Place du Général de Gaulle', '76000-76540', null, 49.443232, 1.099971),
            'phone' => '+33234823644',
        ]);
        $committee6->approved('2017-03-19 09:17:24');
        $committee6->setCurrentElection(new CommitteeElection($this->getReference('designation-1', Designation::class)));
        $this->addReference('committee-6', $committee6);

        $committee7 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_7_UUID,
            'created_by' => LoadAdherentData::ADHERENT_10_UUID,
            'created_at' => '2017-03-19 08:14:45',
            'name' => 'En Marche - Comité de Berlin',
            'description' => 'En Marche pour la France et nos partenaires Allemands.',
            'address' => NullablePostAddress::createAddress('DE', '10369', 'Berlin', '7 Hohenschönhauser Str.', null, null, 52.5330939, 13.4662418),
            'phone' => '+492211653540',
        ]);
        $committee7->approved('2017-03-19 13:43:26');
        $committee7->setCurrentElection(new CommitteeElection($this->getReference('designation-1', Designation::class)));
        $this->addReference('committee-7', $committee7);

        $committee8 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_8_UUID,
            'created_by' => LoadAdherentData::ADHERENT_11_UUID,
            'created_at' => '2017-04-10 17:34:18',
            'name' => 'En Marche - Comité de Singapour',
            'description' => 'En Marche pour la France mais depuis Singapour.',
            'address' => NullablePostAddress::createAddress('SG', '368645', 'Singapour', '47 Jln Mulia', null, null, 1.3329126, 103.8795163),
            'phone' => '+6566888868',
        ]);
        $committee8->approved('2017-04-10 20:23:18');
        $committee8->setCurrentElection(new CommitteeElection($this->getReference('designation-5', Designation::class)));
        $this->addReference('committee-8', $committee8);

        $committee9 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_9_UUID,
            'created_by' => LoadAdherentData::ADHERENT_12_UUID,
            'created_at' => '2017-04-09 12:16:22',
            'name' => 'En Marche - Comité de New York City',
            'description' => 'Les expats sont en En Marche.',
            'address' => NullablePostAddress::createAddress('US', '10019', 'New York', '226 W 52nd St', null, null, 40.7625289, -73.9859927),
            'phone' => '+12123150100',
        ]);
        $committee9->approved('2017-04-09 13:27:42');
        $committee9->setCurrentElection(new CommitteeElection($this->getReference('designation-5', Designation::class)));
        $this->addReference('committee-9', $committee9);

        $committee10 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_10_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2021-01-02 12:18:22',
            'name' => 'En Marche - Suisse',
            'description' => 'En Marche pour la France et nos partenaires en Suisse.',
            'address' => NullablePostAddress::createAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, null, 47.3950786, 8.5361402),
            'phone' => '+33673654349',
        ]);
        $committee10->approved('2021-01-02 13:17:42');
        $committee10->setCurrentElection(new CommitteeElection($this->getReference('designation-5', Designation::class)));
        $this->addReference('committee-10', $committee10);

        $committee11 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_11_UUID,
            'created_by' => LoadAdherentData::ADHERENT_7_UUID,
            'created_at' => '2017-05-12 12:18:22',
            'name' => 'En Marche - Suisse refused',
            'description' => 'En Marche pour la France et nos partenaires en Suisse. (refused)',
            'address' => NullablePostAddress::createAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, null, 47.3950786, 8.5361402),
            'phone' => '+33673654567',
        ]);
        $committee11->approved('2017-05-13 13:17:42');
        $committee11->refused('2017-05-14 13:17:42');
        $this->addReference('committee-11', $committee11);

        $committee12 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_12_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Allemagne',
            'description' => 'En Marche Allemagne.',
            'address' => NullablePostAddress::createAddress('DE', '10789', 'Berlin', 'Breitscheidplatz', null, null, 52.5065133, 13.1445545),
            'phone' => '+33673654349',
        ]);
        $committee12->approved('2020-10-29 12:00:00');
        $committee12->setCurrentElection(new CommitteeElection($this->getReference('designation-5', Designation::class)));
        $this->addReference('committee-12', $committee12);

        $committee13 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_13_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Prague',
            'description' => 'En Marche Prague',
            'address' => NullablePostAddress::createAddress('CZ', '12000', 'Prague', 'Vinohradská 17-11', null, null, 50.078647, 14.434630),
            'phone' => '+33673654349',
        ]);
        $committee13->approved('2020-10-29 12:00:00');
        $committee13->setCurrentElection(new CommitteeElection($this->getReference('designation-8', Designation::class)));
        $this->addReference('committee-13', $committee13);

        $committee14 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_14_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Allemagne 2',
            'description' => 'En Marche Allemagne.',
            'address' => NullablePostAddress::createAddress('DE', '10789', 'Berlin', 'Breitscheidplatz', null, null, 52.5065133, 13.1445545),
            'phone' => '+33673654349',
        ]);
        $committee14->approved('2020-10-29 12:00:00');
        $committee14->setCurrentElection(new CommitteeElection($this->getReference('designation-8', Designation::class)));
        $this->addReference('committee-14', $committee14);

        $committee15 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_15_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2020-10-29 09:00:00',
            'name' => 'En Marche - Allemagne 3',
            'description' => 'En Marche Allemagne.',
            'address' => NullablePostAddress::createAddress('DE', '10789', 'Berlin', 'Breitscheidplatz', null, null, 52.5065133, 13.1445545),
            'phone' => '+33673654349',
        ]);
        $committee15->approved('2020-10-29 12:00:00');
        $committee15->setCurrentElection(new CommitteeElection($this->getReference('designation-9', Designation::class)));
        $this->addReference('committee-15', $committee15);

        $committee16 = $this->committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_16_UUID,
            'created_by' => LoadAdherentData::REFERENT_1_UUID,
            'created_at' => '2021-01-03 09:00:00',
            'name' => 'Une nouvelle demande',
            'description' => 'Nouveau dans l\'année 2021',
            'address' => $this->createNullablePostAddress('824 Avenue du Lys', '77190-77152', null, 48.5182194, 2.6220158),
            'phone' => '+33673654349',
            'status' => Committee::PENDING,
        ]);
        $this->addReference('committee-16', $committee16);

        $manager->persist($committee1);
        $manager->persist($committee2);
        $manager->persist($committee3);
        $manager->persist($committee4);
        $manager->persist($committee5);
        $manager->persist($committee6);
        $manager->persist($committee7);
        $manager->persist($committee8);
        $manager->persist($committee9);
        $manager->persist($committee10);
        $manager->persist($committee11);
        $manager->persist($committee12);
        $manager->persist($committee13);
        $manager->persist($committee14);
        $manager->persist($committee15);
        $manager->persist($committee16);

        // Committee 1
        $manager->persist($membership = $this->getReference('adherent-3', Adherent::class)->followCommittee($committee1, new \DateTime('2017-01-12 13:25:54')));
        $membership->enableVote();
        $manager->persist($this->getReference('adherent-2', Adherent::class)->followCommittee($committee1));
        $manager->persist($this->getReference('adherent-4', Adherent::class)->followCommittee($committee1));

        // Committee 2
        $manager->persist($this->getReference('adherent-6', Adherent::class)->followCommittee($committee2));
        $manager->persist($this->getReference('adherent-84', Adherent::class)->followCommittee($committee2));

        // Committee 3
        $manager->persist($membership = $this->getReference('adherent-7', Adherent::class)->followCommittee($committee3, new \DateTime('2017-01-26 16:08:24')));
        $membership->enableVote();
        $manager->persist($this->getReference('adherent-85', Adherent::class)->hostCommittee($committee3));
        $manager->persist($this->getReference('adherent-86', Adherent::class)->followCommittee($committee3));

        // Committee 4
        $manager->persist($this->getReference('adherent-87', Adherent::class)->followCommittee($committee4));
        $manager->persist($this->getReference('adherent-88', Adherent::class)->followCommittee($committee4));
        $manager->persist($this->getReference('adherent-19', Adherent::class)->followCommittee($committee4));

        // Committee 5
        $manager->persist($this->getReference('adherent-61', Adherent::class)->followCommittee($committee5));
        $manager->persist($this->getReference('adherent-62', Adherent::class)->followCommittee($committee16, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-63', Adherent::class)->followCommittee($committee5));
        $manager->persist($this->getReference('adherent-21', Adherent::class)->followCommittee($committee5));
        $manager->persist($this->getReference('adherent-9', Adherent::class)->followCommittee($committee5));
        $manager->persist($membership = $this->getReference('adherent-64', Adherent::class)->followCommittee($committee5));
        $membership->enableVote();

        // Committee 6
        $manager->persist($membership = $this->getReference('adherent-65', Adherent::class)->followCommittee($committee6));
        $membership->enableVote();
        $manager->persist($this->getReference('adherent-66', Adherent::class)->followCommittee($committee6));
        $manager->persist($this->getReference('adherent-67', Adherent::class)->followCommittee($committee6));

        // Committee 7
        $manager->persist($membership = $this->getReference('adherent-10', Adherent::class)->followCommittee($committee7));
        $membership->enableVote();
        $manager->persist($membership = $this->getReference('adherent-20', Adherent::class)->followCommittee($committee7));
        $membership->enableVote();
        $manager->persist($this->getReference('adherent-68', Adherent::class)->followCommittee($committee7));
        $manager->persist($this->getReference('assessor-1', Adherent::class)->followCommittee($committee7, new \DateTime('-2 months')));

        // Committee 8
        $manager->persist($membership = $this->getReference('adherent-11', Adherent::class)->followCommittee($committee8));
        $membership->enableVote();
        $manager->persist($this->getReference('adherent-69', Adherent::class)->followCommittee($committee8));

        // Committee 9
        $manager->persist($membership = $this->getReference('adherent-12', Adherent::class)->followCommittee($committee9));
        $membership->enableVote();
        $manager->persist($this->getReference('adherent-70', Adherent::class)->followCommittee($committee9));
        $manager->persist($this->getReference('adherent-71', Adherent::class)->followCommittee($committee9));

        // Committee 10
        $manager->persist($membership = $this->getReference('adherent-8', Adherent::class)->followCommittee($committee10));
        $membership->enableVote();
        $manager->persist($this->getReference('adherent-72', Adherent::class)->followCommittee($committee10));
        $manager->persist($this->getReference('adherent-14', Adherent::class)->followCommittee($committee10));

        // Committee 11
        $manager->persist($this->getReference('adherent-13', Adherent::class)->followCommittee($committee11));
        $manager->persist($this->getReference('adherent-73', Adherent::class)->followCommittee($committee11));

        // Committee 12
        $manager->persist($this->getReference('adherent-74', Adherent::class)->hostCommittee($committee12, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-75', Adherent::class)->followCommittee($committee12, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-76', Adherent::class)->followCommittee($committee12, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-77', Adherent::class)->followCommittee($committee12));
        $manager->persist($this->getReference('adherent-78', Adherent::class)->followCommittee($committee12));

        // Committee 13
        $manager->persist($this->getReference('adherent-79', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-22', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-23', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-24', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-25', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-26', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-27', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-28', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-29', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-30', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-31', Adherent::class)->followCommittee($committee13, new \DateTime('-2 months')));

        // Committee 14
        $manager->persist($this->getReference('adherent-80', Adherent::class)->followCommittee($committee14, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-81', Adherent::class)->followCommittee($committee14, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-82', Adherent::class)->followCommittee($committee14, new \DateTime('-2 months')));
        $manager->persist($this->getReference('adherent-83', Adherent::class)->followCommittee($committee14, new \DateTime('-2 months')));

        // Committee 15
        $manager->persist($this->getReference('adherent-32', Adherent::class)->followCommittee($committee15, new \DateTime('-2 months')));
        foreach (range(33, 50) as $index) {
            $manager->persist($this->getReference('adherent-'.$index, Adherent::class)->followCommittee($committee15, new \DateTime('-2 months')));
        }

        $manager->flush();

        $manager->getRepository(Committee::class)->updateMembershipsCounters();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadDesignationData::class,
            LoadGeoZoneData::class,
        ];
    }
}
