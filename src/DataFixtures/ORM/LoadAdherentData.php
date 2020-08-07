<?php

namespace App\DataFixtures\ORM;

use App\Committee\CommitteeFactory;
use App\Coordinator\CoordinatorAreaSectors;
use App\DataFixtures\AutoIncrementResetter;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentCharter\MunicipalChiefCharter;
use App\Entity\AdherentCharter\ReferentCharter;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\AssessorRoleAssociation;
use App\Entity\BoardMember\BoardMember;
use App\Entity\CommitteeElection;
use App\Entity\CoordinatorManagedArea;
use App\Entity\LreArea;
use App\Entity\MunicipalChiefManagedArea;
use App\Entity\MunicipalManagerRoleAssociation;
use App\Entity\MunicipalManagerSupervisorRole;
use App\Entity\PostAddress;
use App\Entity\ReferentTeamMember;
use App\Entity\SenatorArea;
use App\Entity\SenatorialCandidateManagedArea;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Membership\ActivityPositions;
use App\Membership\AdherentFactory;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadAdherentData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    public const ADHERENT_1_UUID = '313bd28f-efc8-57c9-8ab7-2106c8be9697';
    public const ADHERENT_2_UUID = 'e6977a4d-2646-5f6c-9c82-88e58dca8458';
    public const ADHERENT_3_UUID = 'a046adbe-9c7b-56a9-a676-6151a6785dda';
    public const ADHERENT_4_UUID = '29461c49-6316-5be1-9ac3-17816bf2d819';
    public const ADHERENT_5_UUID = 'b4219d47-3138-5efd-9762-2ef9f9495084';
    public const ADHERENT_6_UUID = 'acc73b03-9743-47d8-99db-5a6c6f55ad67';
    public const ADHERENT_7_UUID = 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4';
    public const ADHERENT_9_UUID = '93de5d98-383a-4863-9f47-eb7a348873a8';
    public const ADHERENT_10_UUID = 'd4b1e7e1-ba18-42a9-ace9-316440b30fa7';
    public const ADHERENT_11_UUID = 'f458cc73-3678-4bd0-8e2f-d1c83be3a7e1';
    public const ADHERENT_12_UUID = 'cd76b8cf-af20-4976-8dd9-eb067a2f30c7';
    public const ADHERENT_13_UUID = '46ab0600-b5a0-59fc-83a7-cc23ca459ca0';
    public const ADHERENT_14_UUID = '511c21bf-1240-5271-abaa-3393d3f40740';
    public const ADHERENT_15_UUID = '0a68eb57-c88a-5f34-9e9d-27f85e68af4f';
    public const ADHERENT_16_UUID = '25e75e2f-2f73-4f51-8542-bd511ba6a945';
    public const ADHERENT_17_UUID = '69fcc468-598a-49ac-a651-d4d3ee856446';
    public const ADHERENT_18_UUID = 'a2acfbda-6d5d-4614-b96a-ba00ab6fc7ee';
    public const ADHERENT_19_UUID = '1529f096-12d7-42bb-8c98-a4966a730e2a';
    public const COORDINATOR_1_UUID = 'd72d88ee-44bf-5059-bd19-02af28f0c7dc';
    public const COORDINATOR_2_UUID = '1ebee762-4dc1-42f6-9884-1c83ba9c6d71';
    public const REFERENT_1_UUID = '29461c49-2646-4d89-9c82-50b3f9b586f4';
    public const REFERENT_2_UUID = '2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf';
    public const REFERENT_3_UUID = 'e1bee762-4dc1-42f6-9884-1c83ba9c6d17';
    public const DEPUTY_1_UUID = '918f07e5-676b-49c0-b76d-72ce01cb2404';
    public const DEPUTY_2_UUID = 'ccd87fb0-7d98-433f-81e1-3dd8b14f79c0';
    public const DEPUTY_3_UUID = '160cdf45-80c4-4663-aa21-0ae23091a381';
    public const SENATOR_UUID = '021268fe-d4b3-44a7-bce9-c001191249a7';
    public const ASSESSOR_UUID = 'ae341e67-6e4c-4ead-b4be-1ade6693d512';
    public const MUNICIPAL_MANAGER_UUID = 'c2ba1ce4-e103-415f-a67a-260b8c651b55';
    public const SENATORIAL_CANDIDATE_UUID = 'ab03c939-8f70-40a8-b2cd-d147ec7fd09e';

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

    public const MUNICIPAL_CHIEF_1_UUID = '15d9154e-22d0-45f4-9b82-7f383342a3b8';
    public const MUNICIPAL_CHIEF_2_UUID = 'bdc66cc7-ddf0-4406-b76a-447acb1594ab';
    public const MUNICIPAL_CHIEF_3_UUID = '991e29ff-0333-4a30-a228-067ac5bbe6a9';

    public const DEFAULT_PASSWORD = 'secret!12345';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'committees');

        $adherentFactory = $this->getAdherentFactory();

        // Create adherent users list
        $adherent1 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'michelle.dufour@example.ch',
            'gender' => 'female',
            'first_name' => 'Michelle',
            'last_name' => 'Dufour',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'birthdate' => '1972-11-23',
        ]);
        $adherent1->addReferentTag($this->getReference('referent_tag_ch'));
        $adherent1->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $this->addReference('adherent-1', $adherent1);

        $adherent2 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'carl999@example.fr',
            'gender' => 'male',
            'nickname' => 'pont',
            'nationality' => 'FR',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', null, 45.570898, 5.927206),
            'birthdate' => '1950-07-08',
            'position' => 'retired',
            'phone' => '33 0111223344',
            'registered_at' => '2016-11-16 20:45:33',
        ]);
        $adherent2->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent2->removeSubscriptionType($this->getReference('st-'.SubscriptionTypeEnum::LOCAL_HOST_EMAIL));

        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent2->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent2->addReferentTag($this->getReference('referent_tag_73'));
        $this->addReference('adherent-2', $adherent2);

        $adherent3 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'jacques.picard@en-marche.fr',
            'gender' => 'male',
            'nickname' => 'kikouslove',
            'nationality' => 'FR',
            'nickname_used' => true,
            'first_name' => 'Jacques',
            'last_name' => 'Picard',
            'address' => PostAddress::createFrenchAddress('36 rue de la Paix', '75008-75108', null, 48.8699464, 2.3297187),
            'birthdate' => '1953-04-03',
            'position' => 'retired',
            'phone' => '33 187264236',
            'registered_at' => '2017-01-03 08:47:54',
        ]);
        $adherent3->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent3->addReferentTag($this->getReference('referent_tag_75'));
        $adherent3->addReferentTag($this->getReference('referent_tag_75008'));
        $adherent3->addTag($this->getReference('adherent_tag_at007'));
        $terco75 = $this->getReference('terco_75');
        $quality = new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::DESIGNATED_ADHERENT, 'Paris 75');
        $membershipTC = new TerritorialCouncilMembership($terco75, $adherent3, new \DateTime('2020-06-06'));
        $membershipTC->addQuality($quality);
        $manager->persist($membershipTC);
        $this->addReference('adherent-3', $adherent3);

        $adherent4 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_4_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'luciole1989@spambox.fr',
            'gender' => 'female',
            'nationality' => 'FR',
            'first_name' => 'Lucie',
            'last_name' => 'Olivera',
            'address' => PostAddress::createFrenchAddress('13 boulevard des Italiens', '75009-75109', null, 48.8713224, 2.3353755),
            'birthdate' => '1989-09-17',
            'position' => 'student',
            'phone' => '33 727363643',
            'registered_at' => '2017-01-18 13:15:28',
        ]);
        $adherent4->setPosition(ActivityPositions::UNEMPLOYED);
        $adherent4->setInterests(['jeunesse']);
        $adherent4->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent4->removeSubscriptionTypeByCode(SubscriptionTypeEnum::DEPUTY_EMAIL);
        $adherent4->setProcurationManagedAreaCodesAsString('75, 44, GB, 92130, 91300');
        $adherent4->addReferentTag($this->getReference('referent_tag_75'));
        $adherent4->addReferentTag($this->getReference('referent_tag_75009'));
        $quality = new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::LRE_MANAGER, 'Paris 75');
        $membershipTC = new TerritorialCouncilMembership($terco75, $adherent4, new \DateTime('2020-07-07'));
        $membershipTC->addQuality($quality);
        $manager->persist($membershipTC);
        $this->addReference('adherent-4', $adherent4);

        $adherent5 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_5_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'gisele-berthoux@caramail.com',
            'gender' => 'female',
            'nationality' => 'FR',
            'first_name' => 'Gisele',
            'last_name' => 'Berthoux',
            'address' => PostAddress::createFrenchAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684),
            'birthdate' => '1983-12-24',
            'position' => 'unemployed',
            'phone' => '33 138764334',
            'registered_at' => '2017-01-08 05:55:43',
        ]);
        $adherent5->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent5->removeSubscriptionTypeByCode(SubscriptionTypeEnum::MUNICIPAL_EMAIL);
        $adherent5->addReferentTag($this->getReference('referent_tag_92'));
        $this->addReference('adherent-5', $adherent5);

        $adherent6 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_6_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'benjyd@aol.com',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Benjamin',
            'last_name' => 'Duroc',
            'address' => PostAddress::createFrenchAddress('39 rue de Crimée', '13003-13203', null, 43.3062866, 5.3791498),
            'birthdate' => '1987-02-08',
            'position' => 'employed',
            'phone' => '33 673643424',
            'registered_at' => '2017-01-16 18:33:22',
        ]);
        $adherent6->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent6->addTag($this->getReference('adherent_tag_at001'));
        $adherent6->addTag($this->getReference('adherent_tag_at002'));
        $adherent6->addTag($this->getReference('adherent_tag_at003'));
        $adherent6->addTag($this->getReference('adherent_tag_at007'));
        $adherent6->addReferentTag($this->getReference('referent_tag_13'));
        $this->addReference('adherent-6', $adherent6);

        $adherent7 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_7_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'francis.brioul@yahoo.com',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Francis',
            'last_name' => 'Brioul',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-01-07',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $adherent7->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent7->addReferentTag($this->getReference('referent_tag_77'));
        $this->addReference('adherent-7', $adherent7);

        $adherent9 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_9_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'laura@deloche.com',
            'gender' => 'female',
            'nationality' => 'FR',
            'first_name' => 'Laura',
            'last_name' => 'Deloche',
            'address' => PostAddress::createFrenchAddress('2 Place du Général de Gaulle', '76000-76540', null, 49.443232, 1.099971),
            'birthdate' => '1973-04-11',
            'position' => 'employed',
            'phone' => '33 234823644',
            'registered_at' => '2017-02-16 17:12:08',
        ]);
        $adherent9->setLegislativeCandidate(true);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent9->setBoardMember(BoardMember::AREA_FRANCE_METROPOLITAN, $roles);
        $adherent9->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent9->addReferentTag($this->getReference('referent_tag_76'));
        $this->addReference('adherent-9', $adherent9);

        $adherent10 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_10_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'martine.lindt@gmail.com',
            'gender' => 'female',
            'nationality' => 'DE',
            'first_name' => 'Martine',
            'last_name' => 'Lindt',
            'address' => PostAddress::createForeignAddress('DE', '10369', 'Berlin', '7 Hohenschönhauser Str.', null, 52.5330939, 13.4662418),
            'birthdate' => '2000-11-14',
            'position' => 'student',
            'phone' => '49 2211653540',
            'registered_at' => '2017-02-23 13:56:12',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent10->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent10->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent10->addReferentTag($this->getReference('referent_tag_de'));
        $this->addReference('adherent-10', $adherent10);

        $adherent11 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_11_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'lolodie.dutemps@hotnix.tld',
            'gender' => 'female',
            'nationality' => 'SG',
            'first_name' => 'Élodie',
            'last_name' => 'Dutemps',
            'address' => PostAddress::createForeignAddress('SG', '368645', 'Singapour', '47 Jln Mulia', null, 1.3329126, 103.8795163),
            'birthdate' => '2002-07-13',
            'position' => 'employed',
            'phone' => '65 66888868',
            'registered_at' => '2017-04-10 14:08:12',
        ]);
        $adherent11->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent11->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent11->addReferentTag($this->getReference('referent_tag_sg'));
        $this->addReference('adherent-11', $adherent11);

        $adherent12 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_12_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'kiroule.p@blabla.tld',
            'gender' => 'male',
            'nationality' => 'US',
            'first_name' => 'Pierre',
            'last_name' => 'Kiroule',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', null, 40.7625289, -73.9859927),
            'birthdate' => '1964-10-02',
            'position' => 'employed',
            'phone' => '1 2123150100',
            'registered_at' => '2017-04-09 06:20:38',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('adherent'));
        $adherent12->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $adherent12->getBoardMember()->addSavedBoardMember($adherent11->getBoardMember());
        $adherent12->getBoardMember()->addSavedBoardMember($adherent10->getBoardMember());
        $adherent12->getBoardMember()->addSavedBoardMember($adherent9->getBoardMember());
        $adherent12->getBoardMember()->addSavedBoardMember($adherent2->getBoardMember());
        $adherent12->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent12->setLegislativeCandidate(true);
        $adherent12->addReferentTag($this->getReference('referent_tag_us'));
        $this->addReference('adherent-12', $adherent12);

        $adherent13 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_13_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'michel.vasseur@example.ch',
            'gender' => 'male',
            'first_name' => 'Michel',
            'last_name' => 'VASSEUR',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '12 Pilgerweg', null, 47.321569, 8.549968799999988),
            'birthdate' => '1987-05-13',
        ]);
        $adherent13->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent13->addReferentTag($this->getReference('referent_tag_ch'));
        $adherent13->setMandates(['european_deputy']);
        $lreArea = new LreArea();
        $lreArea->setAllTags(true);
        $adherent13->setLreArea($lreArea);
        $this->addReference('adherent-13', $adherent13);

        $adherent14 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_14_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'damien.schmidt@example.ch',
            'gender' => 'male',
            'first_name' => 'Damien',
            'last_name' => 'SCHMIDT',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', 'Seestrasse 204', null, 47.3180696, 8.552615),
            'birthdate' => '1988-04-13',
        ]);
        $adherent14->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent14->addReferentTag($this->getReference('referent_tag_ch'));

        // Non activated, enabled adherent
        $adherent15 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_15_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'thomas.leclerc@example.ch',
            'gender' => 'male',
            'first_name' => 'Thomas',
            'last_name' => 'Leclerc',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'birthdate' => '1982-05-12',
            'registered_at' => '2017-04-09 06:20:38',
        ]);
        $adherent15->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent15->setStatus(Adherent::ENABLED);
        $adherent15->addReferentTag($this->getReference('referent_tag_ch'));
        $this->addReference('adherent-15', $adherent15);

        $adherent16 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_16_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'commissaire.biales@example.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Patrick',
            'last_name' => 'Bialès',
            'address' => PostAddress::createFrenchAddress('26 Rue Louis Blanc', '75000-75010', null, 50.649561, 3.0644126),
            'birthdate' => '1950-07-25',
            'position' => 'commissioner',
            'phone' => '33 712345678',
            'registered_at' => '1994-03-09 00:00:00',
        ]);
        $adherent16->setPosition(ActivityPositions::EMPLOYED);
        $adherent16->setAssessorManagedAreaCodesAsString('93, 59, UK');

        $adherent17 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_17_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'jean-claude.dusse@example.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Jean-Claude',
            'last_name' => 'Dusse',
            'address' => PostAddress::createFrenchAddress('13 Avenue du Peuple Belge', '59000-59350', null, 50.6420374, 3.0630445),
            'birthdate' => '1952-04-16',
            'position' => 'commissioner',
            'phone' => '33 712345678',
            'registered_at' => '1994-03-09 00:00:00',
        ]);
        $adherent17->setPosition(ActivityPositions::EMPLOYED);
        $adherent17->setStatus(Adherent::ENABLED);
        $adherent17->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $this->addReference('municipal-manager-lille', $adherent17);

        $adherent18 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_18_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'bernard.morin@example.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'first_name' => 'Bernard',
            'last_name' => 'Morin',
            'address' => PostAddress::createFrenchAddress('230 Rue du Moulin', '59246-59411', null, 50.481964435189084, 3.10317921728396),
            'birthdate' => '1951-05-04',
            'position' => 'commissioner',
            'phone' => '33 712345678',
            'registered_at' => '1994-03-09 00:00:00',
        ]);
        $adherent18->setPosition(ActivityPositions::EMPLOYED);
        $adherent18->setStatus(Adherent::ENABLED);
        $adherent18->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $this->addReference('municipal-manager-roubaix', $adherent18);

        $adherent19 = $adherentFactory->createFromArray([
            'uuid' => self::ADHERENT_19_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'cedric.lebon@en-marche-dev.fr',
            'gender' => 'male',
            'nationality' => 'FR',
            'nickname_used' => true,
            'first_name' => 'Cédric',
            'last_name' => 'Lebon',
            'address' => PostAddress::createFrenchAddress('36 rue de la Paix', '75008-75108', null, 48.8699464, 2.3297187),
            'birthdate' => '1967-01-03',
            'position' => 'retired',
            'phone' => '33 187264236',
            'registered_at' => '2018-01-03 08:47:54',
        ]);
        $adherent3->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $adherent3->addReferentTag($this->getReference('referent_tag_75'));
        $adherent3->addReferentTag($this->getReference('referent_tag_75008'));

        $this->addReference('adherent-20', $adherent19);

        $referent = $adherentFactory->createFromArray([
            'uuid' => self::REFERENT_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'referent@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Referent',
            'last_name' => 'Referent',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-01-07',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $referent->setReferent(
            [
                $this->getReference('referent_tag_ch'),
                $this->getReference('referent_tag_es'),
                $this->getReference('referent_tag_92'),
                $this->getReference('referent_tag_76'),
                $this->getReference('referent_tag_77'),
                $this->getReference('referent_tag_13'),
                $this->getReference('referent_tag_59'),
            ],
            -1.6743,
            48.112
        );
        $roles = new ArrayCollection();
        $roles->add($this->getReference('referent'));
        $referent->setBoardMember(BoardMember::AREA_FRANCE_METROPOLITAN, $roles);
        $referent->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $referent->addReferentTag($this->getReference('referent_tag_77'));
        $referent->addCharter(new ReferentCharter());
        $referent->setLreArea(new LreArea($this->getReference('referent_tag_76')));
        $this->addReference('adherent-8', $referent);

        $referent75and77 = $adherentFactory->createFromArray([
            'uuid' => self::REFERENT_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'referent-75-77@en-marche-dev.fr',
            'gender' => 'female',
            'first_name' => 'Referent75and77',
            'last_name' => 'Referent75and77',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '75001-75101', null, 48.5278939, 2.6484923),
            'birthdate' => '1970-01-08',
            'position' => 'employed',
            'phone' => '33 6765204050',
            'registered_at' => '2018-05-12 12:31:45',
        ]);
        $referent75and77->setReferent(
            [
                $this->getReference('referent_tag_77'),
                $this->getReference('referent_tag_75008'),
                $this->getReference('referent_tag_75009'),
            ],
            -1.6743,
            48.112
        );
        $referent75and77->setPrintPrivilege(true);
        $referent75and77->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $referent75and77->addReferentTag($this->getReference('referent_tag_75'));
        $quality = new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Paris 75');
        $membershipTC = new TerritorialCouncilMembership($terco75, $referent75and77, new \DateTime('2020-02-02'));
        $membershipTC->addQuality($quality);
        $manager->persist($membershipTC);
        $this->addReference('adherent-19', $referent75and77);

        $referentChild = $adherentFactory->createFromArray([
            'uuid' => self::REFERENT_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'referent-child@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Referent child',
            'last_name' => 'Referent child',
            'address' => PostAddress::createFrenchAddress('3 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1962-02-07',
            'position' => 'employed',
            'phone' => '33 673654348',
            'registered_at' => '2017-01-25 19:31:45',
        ]);
        $referentChild->setReferent(
            [
                $this->getReference('referent_tag_ch'),
                $this->getReference('referent_tag_93'),
            ],
            -1.6743,
            48.112
        );
        $referentChild->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $referentChild->addReferentTag($this->getReference('referent_tag_77'));

        $coordinator = $adherentFactory->createFromArray([
            'uuid' => self::COORDINATOR_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'coordinateur@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Coordinateur',
            'last_name' => 'Coordinateur',
            'address' => PostAddress::createFrenchAddress('75 Avenue Aristide Briand', '94110-94003', null, 48.805347, 2.325805),
            'birthdate' => '1969-04-10',
            'position' => 'employed',
            'phone' => '33 665859053',
            'registered_at' => '2017-09-20 15:31:21',
        ]);
        $coordinator->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $coordinator->setCoordinatorCommitteeArea(new CoordinatorManagedArea(['FR'], CoordinatorAreaSectors::COMMITTEE_SECTOR));
        $coordinator->addReferentTag($this->getReference('referent_tag_94'));

        $coordinatorCP = $adherentFactory->createFromArray([
            'uuid' => self::COORDINATOR_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'coordinatrice-cp@en-marche-dev.fr',
            'gender' => 'female',
            'first_name' => 'Coordinatrice',
            'last_name' => 'CITIZEN PROJECT',
            'address' => PostAddress::createFrenchAddress('Place de la Madeleine', '75008-75108', null, 48.8704135, 2.324256),
            'birthdate' => '1989-03-13',
            'position' => 'employed',
            'phone' => '33 665859053',
            'registered_at' => '2017-09-20 15:31:21',
        ]);
        $coordinatorCP->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $coordinatorCP->setCoordinatorCitizenProjectArea(new CoordinatorManagedArea(['US', '59290', '77'], CoordinatorAreaSectors::CITIZEN_PROJECT_SECTOR));
        $coordinatorCP->addReferentTag($this->getReference('referent_tag_75'));
        $coordinatorCP->addReferentTag($this->getReference('referent_tag_75008'));
        $this->addReference('adherent-17', $coordinatorCP);

        $deputy_75_1 = $adherentFactory->createFromArray([
            'uuid' => self::DEPUTY_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'deputy@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Député',
            'last_name' => 'PARIS I',
            'address' => PostAddress::createFrenchAddress('3 Avenue du Général Eisenhower', '75008-75108', null, 48.8665777, 2.311635),
            'birthdate' => '1982-06-02',
            'registered_at' => '2017-06-01 09:26:31',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('deputy'));
        $deputy_75_1->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $deputy_75_1->setBoardMember(BoardMember::AREA_FRANCE_METROPOLITAN, $roles);
        $deputy_75_1->addReferentTag($this->getReference('referent_tag_75'));
        $deputy_75_1->addReferentTag($this->getReference('referent_tag_75008'));
        $this->addReference('deputy-75-1', $deputy_75_1);

        $deputy_75_2 = $adherentFactory->createFromArray([
            'uuid' => self::DEPUTY_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'deputy-75-2@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Député',
            'last_name' => 'PARIS II',
            'address' => PostAddress::createFrenchAddress('26 rue Vivienne', '75002-75102', null, 48.870025, 2.340985),
            'birthdate' => '1975-04-01',
            'registered_at' => '2018-08-05 15:02:34',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('deputy'));
        $deputy_75_2->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $deputy_75_2->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $deputy_75_2->addReferentTag($this->getReference('referent_tag_75'));
        $deputy_75_1->addReferentTag($this->getReference('referent_tag_75002'));
        $this->addReference('deputy-75-2', $deputy_75_2);

        $deputy_ch_li = $adherentFactory->createFromArray([
            'uuid' => self::DEPUTY_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'deputy-ch-li@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Député',
            'last_name' => 'CHLI FDESIX',
            'address' => PostAddress::createFrenchAddress('1 Place Colette', '75001-75101', null, 48.863571, 2.335938),
            'birthdate' => '1979-07-02',
            'registered_at' => '2017-06-26 10:15:17',
        ]);
        $roles = new ArrayCollection();
        $roles->add($this->getReference('deputy'));
        $deputy_ch_li->setSubscriptionTypes($this->getStandardSubscriptionTypes());
        $deputy_ch_li->setBoardMember(BoardMember::AREA_ABROAD, $roles);
        $deputy_ch_li->addReferentTag($this->getReference('referent_tag_ch'));
        $this->addReference('deputy-ch-li', $deputy_ch_li);

        // senator
        $senator_59 = $adherentFactory->createFromArray([
            'uuid' => self::SENATOR_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'senateur@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Bob',
            'last_name' => 'Senateur (59)',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1992-07-28',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $senatorArea = new SenatorArea();
        $senatorArea->setDepartmentTag($this->getReference('referent_tag_59'));
        $senator_59->setSenatorArea($senatorArea);
        $this->addReference('senator-59', $senator_59);

        // municipal chief
        $municipalChief1 = $adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_CHIEF_1_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'municipal-chief@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Municipal 1',
            'last_name' => 'Chef 1',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1992-07-28',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $municipalChief1->addCharter(new MunicipalChiefCharter());
        $municipalChiefArea1 = new MunicipalChiefManagedArea();
        $municipalChiefArea1->setInseeCode('59350');
        $municipalChiefArea1->setJecouteAccess(true);
        $municipalChief1->setMunicipalChiefManagedArea($municipalChiefArea1);
        $this->addReference('municipal-chief-1', $municipalChief1);

        $municipalChief2 = $adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_CHIEF_2_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'municipal-chief-2@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Municipal 2',
            'last_name' => 'Chef 2',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $municipalChiefArea2 = new MunicipalChiefManagedArea();
        $municipalChiefArea2->setInseeCode('59124');
        $municipalChief2->setMunicipalChiefManagedArea($municipalChiefArea2);
        $this->addReference('municipal-chief-2', $municipalChief2);

        $municipalChief3 = $adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_CHIEF_3_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'municipal-chief-3@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Municipal 3',
            'last_name' => 'Chef 3',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $municipalChiefArea3 = new MunicipalChiefManagedArea();
        $municipalChiefArea3->setInseeCode('59411');
        $municipalChief3->setMunicipalChiefManagedArea($municipalChiefArea3);
        $this->addReference('municipal-chief-3', $municipalChief3);

        $assessor = $adherentFactory->createFromArray([
            'uuid' => self::ASSESSOR_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'assesseur@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Bob',
            'last_name' => 'Assesseur',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $assessor->setAssessorRole(new AssessorRoleAssociation($this->getReference('vote-place-lille-wazemmes')));
        $assessor->setElectionResultsReporter(true);
        $this->addReference('assessor-1', $assessor);

        $municipalManager = $adherentFactory->createFromArray([
            'uuid' => self::MUNICIPAL_MANAGER_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'responsable-communal@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Bob',
            'last_name' => 'Responsable Communal',
            'address' => PostAddress::createFrenchAddress('12 Avenue du Peuple Belge', '59000-59350', null, 50.6420374, 3.0630445),
            'birthdate' => '1983-08-27',
            'position' => 'employed',
            'phone' => '33 673654350',
            'registered_at' => '2019-07-10 09:19:00',
        ]);
        $municipalManager->setMunicipalManagerRole(new MunicipalManagerRoleAssociation([$this->getReference('city-lille')]));
        $this->addReference('municipal-manager-1', $municipalManager);

        $senatorialCandidate = $adherentFactory->createFromArray([
            'uuid' => self::SENATORIAL_CANDIDATE_UUID,
            'password' => self::DEFAULT_PASSWORD,
            'email' => 'senatorial-candidate@en-marche-dev.fr',
            'gender' => 'male',
            'first_name' => 'Senatorial',
            'last_name' => 'Candidate 1',
            'address' => PostAddress::createFrenchAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            'birthdate' => '1982-08-27',
            'position' => 'employed',
            'phone' => '33 673654349',
            'registered_at' => '2019-06-10 09:19:00',
        ]);
        $senatorialCandidateManagedArea = new SenatorialCandidateManagedArea();
        $senatorialCandidateManagedArea->addDepartmentTag($this->getReference('referent_tag_59'));
        $senatorialCandidate->setSenatorialCandidateManagedArea($senatorialCandidateManagedArea);
        $this->addReference('senatorial-candidate', $senatorialCandidate);

        // Create adherents accounts activation keys
        $key1 = AdherentActivationToken::generate($adherent1);
        $key2 = AdherentActivationToken::generate($adherent2);
        $key3 = AdherentActivationToken::generate($adherent3);
        $key4 = AdherentActivationToken::generate($adherent4);
        $key5 = AdherentActivationToken::generate($adherent5);
        $key6 = AdherentActivationToken::generate($adherent6);
        $key7 = AdherentActivationToken::generate($adherent7);
        $key8 = AdherentActivationToken::generate($referent);
        $key9 = AdherentActivationToken::generate($adherent9);
        $key10 = AdherentActivationToken::generate($adherent10);
        $key11 = AdherentActivationToken::generate($adherent11);
        $key12 = AdherentActivationToken::generate($adherent12);
        $key13 = AdherentActivationToken::generate($adherent13);
        $key14 = AdherentActivationToken::generate($adherent14);
        $key15 = AdherentActivationToken::generate($adherent15);
        $key16 = AdherentActivationToken::generate($coordinator);
        $key17 = AdherentActivationToken::generate($coordinatorCP);
        $key18 = AdherentActivationToken::generate($referentChild);
        $key19 = AdherentActivationToken::generate($referent75and77);
        $key20 = AdherentActivationToken::generate($deputy_75_1);
        $key21 = AdherentActivationToken::generate($deputy_ch_li);
        $key22 = AdherentActivationToken::generate($adherent16);
        $key23 = AdherentActivationToken::generate($municipalChief1);
        $key24 = AdherentActivationToken::generate($municipalChief2);
        $key25 = AdherentActivationToken::generate($municipalChief3);
        $key26 = AdherentActivationToken::generate($adherent17);
        $key27 = AdherentActivationToken::generate($adherent18);
        $key28 = AdherentActivationToken::generate($senator_59);
        $key29 = AdherentActivationToken::generate($assessor);
        $key30 = AdherentActivationToken::generate($municipalManager);
        $key31 = AdherentActivationToken::generate($deputy_75_2);
        $key32 = AdherentActivationToken::generate($adherent19);
        $key33 = AdherentActivationToken::generate($senatorialCandidate);

        // Enable some adherents accounts
        $adherent2->activate($key2, '2016-11-16 20:54:13');
        $adherent3->activate($key3, '2017-01-03 09:12:37');
        $adherent4->activate($key4, '2017-01-18 13:23:50');
        $adherent5->activate($key5, '2017-01-08 06:42:56');
        $adherent6->activate($key6, '2017-01-17 08:07:45');
        $adherent7->activate($key7, '2017-01-25 19:34:02');
        $adherent9->activate($key9, '2017-02-16 17:23:15');
        $adherent10->activate($key10, '2017-02-23 14:02:18');
        $adherent11->activate($key11, '2017-04-10 14:12:56');
        $adherent12->activate($key12, '2017-04-09 06:26:14');
        $adherent13->activate($key13, '2017-05-03 09:16:54');
        $adherent14->activate($key14, '2017-05-04 09:34:21');
        $adherent16->activate($key22, '2017-05-04 09:34:21');
        $adherent17->activate($key26, '2017-06-25 11:36:48');
        $adherent18->activate($key27, '2017-06-25 11:36:48');
        $adherent19->activate($key32, '2017-06-25 11:36:48');
        // $key15 is not activated, but adherent is enabled
        $referent->activate($key8, '2017-02-07 13:20:45');
        $coordinator->activate($key16, '2017-09-20 17:44:32');
        $coordinatorCP->activate($key17, '2018-01-20 14:34:11');
        $referentChild->activate($key18, '2017-02-07 13:20:45');
        $referent75and77->activate($key19, '2018-05-13 07:21:01');
        $deputy_75_1->activate($key20, '2017-06-01 12:14:51');
        $deputy_75_2->activate($key31, '2017-07-26 12:14:51');
        $deputy_ch_li->activate($key21, '2017-06-26 12:14:51');
        $senator_59->activate($key28, '2017-06-26 12:14:51');
        $municipalChief1->activate($key23, '2019-06-10 09:19:00');
        $municipalChief2->activate($key24, '2019-06-10 09:19:00');
        $municipalChief3->activate($key25, '2019-06-10 09:19:00');
        $assessor->activate($key29, '2019-06-10 09:19:00');
        $municipalManager->activate($key30, '2019-07-10 09:19:00');
        $senatorialCandidate->activate($key33, '2019-07-10 09:19:00');

        // Create some default committees and make people join them
        $committeeFactory = $this->getCommitteeFactory();

        $committee1 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_1_UUID,
            'created_by' => (string) $adherent3->getUuid(),
            'created_at' => '2017-01-12 13:25:54',
            'name' => 'En Marche Paris 8',
            'slug' => 'en-marche-paris-8',
            'description' => 'Le comité « En Marche ! » des habitants du 8ème arrondissement de Paris.',
            'address' => PostAddress::createFrenchAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.8705073, 2.3132432),
            'phone' => '33 187264236',
            'facebook_page_url' => 'https://facebook.com/enmarche-paris-8',
            'twitter_nickname' => 'enmarche75008',
        ]);
        $committee1->approved('2017-01-12 15:54:18');
        $this->addReference('committee-1', $committee1);

        $committee2 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_2_UUID,
            'created_by' => (string) $adherent6->getUuid(),
            'created_at' => '2017-01-12 19:34:12',
            'name' => 'En Marche Marseille 3',
            'description' => "En Marche ! C'est aussi à Marseille !",
            'address' => PostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', null, 43.3256095, 5.374416),
            'phone' => '33 673643424',
        ]);
        $this->addReference('committee-2', $committee2);

        $committee3 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_3_UUID,
            'created_by' => (string) $adherent7->getUuid(),
            'created_at' => '2017-01-26 16:08:24',
            'name' => 'En Marche Dammarie-les-Lys',
            'slug' => 'en-marche-dammarie-les-lys',
            'description' => 'Les jeunes avec En Marche !',
            'address' => PostAddress::createFrenchAddress('824 Avenue du Lys', '77190-77152', null, 48.5182194, 2.6220158),
            'phone' => '33 673654349',
            'name_locked' => true,
        ]);
        $committee3->approved('2017-01-27 09:18:33');
        $this->addReference('committee-3', $committee3);

        $committee4 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_4_UUID,
            'created_by' => (string) $adherent7->getUuid(),
            'created_at' => '2017-01-19 08:36:55',
            'name' => 'Antenne En Marche de Fontainebleau',
            'description' => 'Vous êtes Bellifontain ? Nous aussi ! Rejoignez-nous !',
            'address' => PostAddress::createFrenchAddress('40 Rue Grande', '77300-77186', null, 48.4047652, 2.6987591),
            'phone' => '33 673654349',
        ]);
        $committee4->approved();
        $committee4->setCurrentElection(new CommitteeElection($this->getReference('designation-3')));
        $this->addReference('committee-4', $committee4);

        $committee5 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_5_UUID,
            'created_by' => (string) $adherent7->getUuid(),
            'created_at' => '2017-01-19 10:54:28',
            'name' => 'En Marche - Comité de Évry',
            'description' => 'En Marche pour une nouvelle vision, du renouveau pour la France.',
            'address' => PostAddress::createFrenchAddress("Place des Droits de l'Homme et du Citoyen", '91000-91228', null, 48.6241569, 2.4265995),
            'phone' => '33 673654349',
        ]);
        $committee5->approved();
        $committee5->setCurrentElection(new CommitteeElection($this->getReference('designation-2')));
        $this->addReference('committee-5', $committee5);

        $committee6 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_6_UUID,
            'created_by' => (string) $adherent9->getUuid(),
            'created_at' => '2017-03-18 20:12:33',
            'name' => 'En Marche - Comité de Rouen',
            'description' => 'En Marche pour la France et la ville de Rouen.',
            'address' => PostAddress::createFrenchAddress('2 Place du Général de Gaulle', '76000-76540', null, 49.443232, 1.099971),
            'phone' => '33 234823644',
        ]);
        $committee6->approved('2017-03-19 09:17:24');
        $committee6->setCurrentElection(new CommitteeElection($this->getReference('designation-1')));
        $this->addReference('committee-6', $committee6);

        $committee7 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_7_UUID,
            'created_by' => (string) $adherent10->getUuid(),
            'created_at' => '2017-03-19 08:14:45',
            'name' => 'En Marche - Comité de Berlin',
            'description' => 'En Marche pour la France et nos partenaires Allemands.',
            'address' => PostAddress::createForeignAddress('DE', '10369', 'Berlin', '7 Hohenschönhauser Str.', null, 52.5330939, 13.4662418),
            'phone' => '49 2211653540',
        ]);
        $committee7->approved('2017-03-19 13:43:26');
        $committee7->setCurrentElection(new CommitteeElection($this->getReference('designation-1')));
        $this->addReference('committee-7', $committee7);

        $committee8 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_8_UUID,
            'created_by' => (string) $adherent11->getUuid(),
            'created_at' => '2017-04-10 17:34:18',
            'name' => 'En Marche - Comité de Singapour',
            'description' => 'En Marche pour la France mais depuis Singapour.',
            'address' => PostAddress::createForeignAddress('SG', '368645', 'Singapour', '47 Jln Mulia', null, 1.3329126, 103.8795163),
            'phone' => '65 66888868',
        ]);
        $committee8->approved('2017-04-10 20:23:18');
        $committee8->setCurrentElection(new CommitteeElection($this->getReference('designation-5')));
        $this->addReference('committee-8', $committee8);

        $committee9 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_9_UUID,
            'created_by' => (string) $adherent12->getUuid(),
            'created_at' => '2017-04-09 12:16:22',
            'name' => 'En Marche - Comité de New York City',
            'description' => 'Les expats sont en En Marche.',
            'address' => PostAddress::createForeignAddress('US', '10019', 'New York', '226 W 52nd St', null, 40.7625289, -73.9859927),
            'phone' => '1 2123150100',
        ]);
        $committee9->approved('2017-04-09 13:27:42');
        $committee9->setCurrentElection(new CommitteeElection($this->getReference('designation-5')));
        $this->addReference('committee-9', $committee9);

        $committee10 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_10_UUID,
            'created_by' => (string) $referent->getUuid(),
            'created_at' => '2017-05-09 12:18:22',
            'name' => 'En Marche - Suisse',
            'description' => 'En Marche pour la France et nos partenaires en Suisse.',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'phone' => '33 673654349',
        ]);
        $committee10->approved('2017-05-09 13:17:42');
        $committee10->setCurrentElection(new CommitteeElection($this->getReference('designation-5')));
        $this->addReference('committee-10', $committee10);

        $committee11 = $committeeFactory->createFromArray([
            'uuid' => self::COMMITTEE_11_UUID,
            'created_by' => (string) $adherent7->getUuid(),
            'created_at' => '2017-05-12 12:18:22',
            'name' => 'En Marche - Suisse refused',
            'description' => 'En Marche pour la France et nos partenaires en Suisse. (refused)',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', null, 47.3950786, 8.5361402),
            'phone' => '33 673654567',
        ]);
        $committee11->approved('2017-05-13 13:17:42');
        $committee11->refused('2017-05-14 13:17:42');
        $this->addReference('committee-11', $committee11);

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
        $manager->persist($adherent9);
        $manager->persist($adherent10);
        $manager->persist($adherent11);
        $manager->persist($adherent12);
        $manager->persist($adherent13);
        $manager->persist($adherent14);
        $manager->persist($adherent15);
        $manager->persist($adherent16);
        $manager->persist($referent);
        $manager->persist($referent75and77);
        $manager->persist($referentChild);
        $manager->persist($coordinator);
        $manager->persist($coordinatorCP);
        $manager->persist($deputy_75_1);
        $manager->persist($deputy_75_2);
        $manager->persist($deputy_ch_li);
        $manager->persist($senator_59);
        $manager->persist($municipalChief1);
        $manager->persist($municipalChief2);
        $manager->persist($municipalChief3);
        $manager->persist($adherent17);
        $manager->persist($adherent18);
        $manager->persist($adherent19);
        $manager->persist($assessor);
        $manager->persist($municipalManager);
        $manager->persist($senatorialCandidate);

        // For Organizational chart: adherent which is co-referent and municipal manager supervisor in the referent@en-marche-dev.fr team
        $adherent6->setReferentTeamMember(new ReferentTeamMember($this->getReference('adherent-8')));
        $adherent6->setMunicipalManagerSupervisorRole(new MunicipalManagerSupervisorRole($this->getReference('adherent-8')));
        // For Organizational chart: adherent which is co-referent in another referent team
        $adherent4->setReferentTeamMember(new ReferentTeamMember($this->getReference('adherent-19')));

        $adherent14->setJecouteManagedAreaCodesAsString($referent->getManagedArea()->getReferentTagCodesAsString());
        $this->addReference('adherent-14', $adherent14);

        $manager->persist($key1);
        $manager->persist($key2);
        $manager->persist($key3);
        $manager->persist($key4);
        $manager->persist($key5);
        $manager->persist($key6);
        $manager->persist($key7);
        $manager->persist($key8);
        $manager->persist($key9);
        $manager->persist($key10);
        $manager->persist($key11);
        $manager->persist($key12);
        $manager->persist($key13);
        $manager->persist($key14);
        $manager->persist($key15);
        $manager->persist($key16);
        $manager->persist($key17);
        $manager->persist($key18);
        $manager->persist($key19);
        $manager->persist($key20);
        $manager->persist($key22);
        $manager->persist($key23);
        $manager->persist($key24);
        $manager->persist($key25);
        $manager->persist($key26);
        $manager->persist($key27);
        $manager->persist($key31);
        $manager->persist($key33);

        $manager->persist($resetPasswordToken);

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

        // Make adherents join committees
        $manager->persist($membership = $adherent7->superviseCommittee($committee3, '2017-01-26 16:08:24'));
        $membership->enableVote();
        $manager->persist($adherent7->superviseCommittee($committee4));
        $manager->persist($adherent7->superviseCommittee($committee5));
        $manager->persist($adherent2->followCommittee($committee1));
        $manager->persist($adherent2->followCommittee($committee6));
        $manager->persist($adherent4->followCommittee($committee1));
        $manager->persist($membership = $adherent5->hostCommittee($committee1));
        $membership->enableVote();
        $manager->persist($adherent6->followCommittee($committee2));
        $manager->persist($adherent4->followCommittee($committee2));
        $manager->persist($membership = $adherent3->superviseCommittee($committee1, '2017-01-12 13:25:54'));
        $membership->enableVote();
        $manager->persist($adherent3->hostCommittee($committee3));
        $manager->persist($adherent3->followCommittee($committee4));
        $manager->persist($adherent3->followCommittee($committee5));
        $manager->persist($adherent3->followCommittee($committee6));
        $manager->persist($adherent3->followCommittee($committee7));
        $manager->persist($adherent3->followCommittee($committee8));
        $manager->persist($adherent3->followCommittee($committee9));
        $manager->persist($membership = $adherent9->superviseCommittee($committee6));
        $membership->enableVote();
        $manager->persist($membership = $adherent10->superviseCommittee($committee7));
        $membership->enableVote();
        $manager->persist($adherent9->followCommittee($committee5));
        $manager->persist($membership = $adherent11->superviseCommittee($committee8));
        $membership->enableVote();
        $manager->persist($membership = $adherent12->superviseCommittee($committee9));
        $membership->enableVote();
        $manager->persist($adherent11->followCommittee($committee9));
        $manager->persist($membership = $referent->superviseCommittee($committee10));
        $membership->enableVote();
        $manager->persist($adherent13->followCommittee($committee10));
        $manager->persist($adherent14->followCommittee($committee10));
        $manager->persist($adherent13->followCommittee($committee11));
        $manager->persist($adherent14->followCommittee($committee11));
        $manager->persist($assessor->followCommittee($committee5));
        $manager->persist($assessor->followCommittee($committee6));
        $manager->persist($assessor->followCommittee($committee7));
        $manager->persist($assessor->followCommittee($committee8));
        $manager->persist($adherent19->followCommittee($committee4));
        $manager->persist($membership = $adherent19->followCommittee($committee5));
        $membership->enableVote();

        $manager->flush();
    }

    private function getAdherentFactory(): AdherentFactory
    {
        return $this->container->get('app.membership.adherent_factory');
    }

    private function getCommitteeFactory(): CommitteeFactory
    {
        return $this->container->get('app.committee.factory');
    }

    private function getStandardSubscriptionTypes(): array
    {
        return array_map(function (string $type) {
            return $this->getReference('st-'.$type);
        }, SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES);
    }

    public function getDependencies()
    {
        return [
            LoadBoardMemberRoleData::class,
            LoadAdherentTagData::class,
            LoadReferentTagData::class,
            LoadSubscriptionTypeData::class,
            LoadVotePlaceData::class,
            LoadCityData::class,
            LoadDesignationData::class,
            LoadTerritorialCouncilData::class,
        ];
    }
}
