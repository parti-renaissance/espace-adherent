<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\CandidateNameEnum;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\ElectedRepresentativeLabel;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\LaREMSupportEnum;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\ElectedRepresentative\SocialLinkTypeEnum;
use App\Entity\ElectedRepresentative\SocialNetworkLink;
use App\Utils\PhoneNumberUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadElectedRepresentativeData extends Fixture implements DependentFixtureInterface
{
    public const ELECTED_REPRESENTATIVE_1_UUID = '34b0b236-b72e-4161-8f9f-7f23f935758f';
    public const ELECTED_REPRESENTATIVE_2_UUID = '4b8bb9fd-0645-47fd-bb9a-3515bf46618a';
    public const ELECTED_REPRESENTATIVE_3_UUID = '84187220-348c-44ef-a297-28e3af88b74b';
    public const ELECTED_REPRESENTATIVE_4_UUID = '07f02feb-86e3-4de9-9970-a1528fd0165e';
    public const ELECTED_REPRESENTATIVE_5_UUID = '2e6a1018-71bd-4ae9-b17c-f93626e306f6';
    public const ELECTED_REPRESENTATIVE_6_UUID = '82ec811a-45f7-4527-97ef-3dea61af131b';
    public const ELECTED_REPRESENTATIVE_7_UUID = '867aed08-9e1a-4ec1-b2da-097e1de70132';
    public const ELECTED_REPRESENTATIVE_8_UUID = '0c62d201-826b-4da7-8424-e8e17935b400';

    public const ELECTED_MANDATE_1_UUID = 'b2afc81d-afd5-4bff-84e5-c95f22242244';
    public const ELECTED_MANDATE_2_UUID = '34d7b4b1-67e9-48fd-b193-373f5076e3f2';
    public const ELECTED_MANDATE_3_UUID = 'e9aa4ef2-3b81-4f47-b460-c1095a217ef8';
    public const ELECTED_MANDATE_4_UUID = '02be8205-3068-419a-8b0c-31ce5b293e5f';
    public const ELECTED_MANDATE_5_UUID = '6206c36f-50bc-4078-a045-2b4842970858';
    public const ELECTED_MANDATE_6_UUID = '639967f6-60fd-4ca5-a9a6-518f0e8b38a0';
    public const ELECTED_MANDATE_7_UUID = 'd3906aa5-b185-4467-b263-87563d0bb6ef';
    public const ELECTED_MANDATE_8_UUID = '23008279-558c-4f46-84f2-3fc182c2ea16';
    public const ELECTED_MANDATE_9_UUID = 'b7ad3756-35d0-4f9b-a196-eebeaf60925a';
    public const ELECTED_MANDATE_10_UUID = '13a6adc8-26dc-4374-a05d-ab74c4e62dac';
    public const ELECTED_MANDATE_11_UUID = 'dc67aae2-68d5-4ac8-9bb0-72588cdee76a';
    public const ELECTED_MANDATE_12_UUID = '03400430-3a75-4b90-83de-ed368f8b3a51';
    public const ELECTED_MANDATE_13_UUID = '920cb618-3967-41ed-98e3-e4e3a6cd66aa';
    public const ELECTED_MANDATE_14_UUID = '1a0fb254-64a1-4846-94a9-4e9ac1338f19';
    public const ELECTED_MANDATE_15_UUID = '9051e0b5-4b56-41b9-8657-cc45e431c727';

    public function load(ObjectManager $manager): void
    {
        // with adherent, mandate 92 CITY_COUNCIL : functions OTHER_MEMBER, PRESIDENT_OF_EPCI
        $erAdherent92 = $this->createElectedRepresentative(
            'Michelle',
            'DUFOUR',
            new \DateTime('1972-11-23'),
            'female',
            self::ELECTED_REPRESENTATIVE_1_UUID
        );
        $erAdherent92->setAdherent($this->getReference('adherent-5', Adherent::class));
        foreach ($erAdherent92->getSponsorships() as $sponsorship) {
            if (2012 === $sponsorship->getPresidentialElectionYear()) {
                $sponsorship->setCandidate(CandidateNameEnum::FRANCOIS_HOLLANDE, $erAdherent92);
            }

            if (2017 === $sponsorship->getPresidentialElectionYear()) {
                $sponsorship->setCandidate(CandidateNameEnum::EMMANUEL_MACRON, $erAdherent92);
            }
        }
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::LAREM,
            $erAdherent92,
            true,
            2017
        );
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_1_UUID),
            MandateTypeEnum::CONSEILLER_MUNICIPAL,
            true,
            null,
            LaREMSupportEnum::OFFICIAL,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_community_200054781'),
            $erAdherent92,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::OTHER_MEMBER,
            'Some precisions',
            $erAdherent92,
            $mandate,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $erAdherent92,
            $mandate,
            false,
            new \DateTime('2015-03-24')
        );
        $erAdherent92->addLabel($label);
        $erAdherent92->addMandate($mandate);
        $erAdherent92->addPoliticalFunction($politicalFunction1);
        $erAdherent92->addPoliticalFunction($politicalFunction2);

        $manager->persist($erAdherent92);

        // with mandate 92 CITY_COUNCIL : functions MAYOR, PRESIDENT_OF_EPCI (finished)
        $erCityCouncilWithFinishedFunction = $this->createElectedRepresentative(
            'Delphine',
            'BOUILLOUX',
            new \DateTime('1977-08-02'),
            'female',
            self::ELECTED_REPRESENTATIVE_2_UUID
        );
        $erCityCouncilWithFinishedFunction->setContactPhone(PhoneNumberUtils::create('+330999887766'));
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::PS,
            $erCityCouncilWithFinishedFunction,
            true,
            2016
        );
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_2_UUID),
            MandateTypeEnum::CONSEILLER_MUNICIPAL,
            true,
            null,
            LaREMSupportEnum::OFFICIAL,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'),
            $erCityCouncilWithFinishedFunction,
            true,
            new \DateTime('2014-03-23')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::MAYOR,
            null,
            $erCityCouncilWithFinishedFunction,
            $mandate,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $erCityCouncilWithFinishedFunction,
            $mandate,
            false,
            new \DateTime('2016-06-02'),
            new \DateTime('2019-01-06')
        );
        $twitter = new SocialNetworkLink('https://twitter.com/DeBou', SocialLinkTypeEnum::TWITTER, $erCityCouncilWithFinishedFunction);
        $instagram = new SocialNetworkLink('https://instagram.com/deBou', SocialLinkTypeEnum::INSTAGRAM, $erCityCouncilWithFinishedFunction);
        $telegram = new SocialNetworkLink('https://telegram.com/deBou', SocialLinkTypeEnum::TELEGRAM, $erCityCouncilWithFinishedFunction);
        $facecbook = new SocialNetworkLink('https://facebook.com/deBou', SocialLinkTypeEnum::FACEBOOK, $erCityCouncilWithFinishedFunction);
        $youtube = new SocialNetworkLink('https://youtube.com/deBou', SocialLinkTypeEnum::YOUTUBE, $erCityCouncilWithFinishedFunction);
        $erCityCouncilWithFinishedFunction->addLabel($label);
        $erCityCouncilWithFinishedFunction->addMandate($mandate);
        $erCityCouncilWithFinishedFunction->addPoliticalFunction($politicalFunction1);
        $erCityCouncilWithFinishedFunction->addPoliticalFunction($politicalFunction2);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($twitter);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($instagram);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($telegram);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($facecbook);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($youtube);

        $manager->persist($erCityCouncilWithFinishedFunction);

        // with mandate 76 CITY_COUNCIL : functions DEPUTY_MAYOR
        // with mandate 76 EPCI_MEMBER (not elected) : functions PRESIDENT_OF_EPCI
        $er2Mandates = $this->createElectedRepresentative(
            'Daniel',
            'BOULON',
            new \DateTime('1951-03-04'),
            null,
            self::ELECTED_REPRESENTATIVE_3_UUID
        );
        $label1 = new ElectedRepresentativeLabel(
            LabelNameEnum::PS,
            $er2Mandates,
            false,
            2014,
            2018
        );
        $label2 = new ElectedRepresentativeLabel(
            LabelNameEnum::GS,
            $er2Mandates,
            true,
            2018
        );
        $mandate1 = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_3_UUID),
            MandateTypeEnum::CONSEILLER_MUNICIPAL,
            true,
            null,
            LaREMSupportEnum::NOT_SUPPORTED,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'),
            $er2Mandates,
            true,
            new \DateTime('2014-03-23')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::DEPUTY_MAYOR,
            null,
            $er2Mandates,
            $mandate1,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate2 = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_4_UUID),
            MandateTypeEnum::CONSEILLER_COMMUNAUTAIRE,
            false,
            null,
            LaREMSupportEnum::NOT_SUPPORTED,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'),
            $er2Mandates,
            true,
            new \DateTime('2017-01-11')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $er2Mandates,
            $mandate2,
            false,
            new \DateTime('2019-07-23')
        );
        $er2Mandates->addLabel($label1);
        $er2Mandates->addLabel($label2);
        $er2Mandates->addMandate($mandate1);
        $er2Mandates->addMandate($mandate2);
        $er2Mandates->addPoliticalFunction($politicalFunction1);
        $er2Mandates->addPoliticalFunction($politicalFunction2);

        $manager->persist($er2Mandates);

        // with mandate 94 SENATOR, no function
        // with mandate 76 DEPUTY finished : functions OTHER_MEMBER
        $er2MandatesOneFinished = $this->createElectedRepresentative(
            'Roger',
            'BUET',
            new \DateTime('1952-04-21'),
            'male',
            self::ELECTED_REPRESENTATIVE_4_UUID
        );
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::OTHER,
            $er2MandatesOneFinished,
            true,
            2014
        );
        $mandate1 = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_5_UUID),
            MandateTypeEnum::SENATEUR,
            true,
            null,
            LaREMSupportEnum::INFORMAL,
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_94'),
            $er2MandatesOneFinished,
            true,
            new \DateTime('2016-03-23')
        );
        $mandate2 = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_6_UUID),
            MandateTypeEnum::DEPUTE,
            true,
            null,
            LaREMSupportEnum::NOT_SUPPORTED,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'),
            $er2MandatesOneFinished,
            false,
            new \DateTime('2011-12-23'),
            new \DateTime('2015-02-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::OTHER_MEMBER,
            null,
            $er2MandatesOneFinished,
            $mandate2,
            false,
            new \DateTime('2019-07-23')
        );
        $er2MandatesOneFinished->addLabel($label);
        $er2MandatesOneFinished->addMandate($mandate1);
        $er2MandatesOneFinished->addMandate($mandate2);
        $er2MandatesOneFinished->addPoliticalFunction($politicalFunction2);

        $manager->persist($er2MandatesOneFinished);

        // with mandate EURO_DEPUTY, no function
        $erEuroDeputy2Labels = $this->createElectedRepresentative(
            'Sans',
            'OFFICIELID',
            new \DateTime('1951-11-03'),
            'male',
            self::ELECTED_REPRESENTATIVE_5_UUID
        );
        $label1 = new ElectedRepresentativeLabel(
            LabelNameEnum::MRC,
            $erEuroDeputy2Labels,
            false,
            2014,
            2017
        );
        $label2 = new ElectedRepresentativeLabel(
            LabelNameEnum::GS,
            $erEuroDeputy2Labels,
            true,
            2017
        );
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_7_UUID),
            MandateTypeEnum::DEPUTE_EUROPEEN,
            true,
            null,
            LaREMSupportEnum::INVESTED,
            null,
            $erEuroDeputy2Labels,
            true,
            new \DateTime('2016-03-23')
        );
        $erEuroDeputy2Labels->addLabel($label1);
        $erEuroDeputy2Labels->addLabel($label2);
        $erEuroDeputy2Labels->addMandate($mandate);

        $manager->persist($erEuroDeputy2Labels);

        // with mandate 13 DEPUTY : functions VICE_PRESIDENT_OF_EPCI
        // with mandate 13 REGIONAL_COUNCIL : functions PRESIDENT_OF_EPCI
        $er2Mandates2Functions = $this->createElectedRepresentative(
            'André',
            'LOBELL',
            new \DateTime('1951-11-03'),
            'male',
            self::ELECTED_REPRESENTATIVE_6_UUID
        );
        $mandate1 = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_8_UUID),
            MandateTypeEnum::DEPUTE,
            true,
            null,
            LaREMSupportEnum::INFORMAL,
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_13'),
            $er2Mandates2Functions,
            true,
            new \DateTime('2015-03-13')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::VICE_PRESIDENT_OF_EPCI,
            null,
            $er2Mandates2Functions,
            $mandate1,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate2 = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_9_UUID),
            MandateTypeEnum::CONSEILLER_REGIONAL,
            true,
            null,
            LaREMSupportEnum::NOT_SUPPORTED,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_76540'),
            $er2Mandates2Functions,
            true,
            new \DateTime('2017-07-18')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::MAYOR_ASSISTANT,
            null,
            $er2Mandates2Functions,
            $mandate2,
            true,
            new \DateTime('2019-05-10')
        );
        $politicalFunction3 = new PoliticalFunction(
            PoliticalFunctionNameEnum::VICE_PRESIDENT_OF_EPCI,
            null,
            $er2Mandates2Functions,
            $mandate2,
            false,
            new \DateTime('2016-05-10'),
            new \DateTime('2019-05-09')
        );
        $er2Mandates2Functions->addMandate($mandate1);
        $er2Mandates2Functions->addMandate($mandate2);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction1);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction2);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction3);
        $er2Mandates2Functions->setAdherent($this->getReference('deputy-75-1', Adherent::class));

        $manager->persist($er2Mandates2Functions);

        // with not elected mandate Corsica CORSICA_ASSEMBLY_MEMBER
        $erWithNotElectedMandate = $this->createElectedRepresentative(
            'Jesuis',
            'PASELU',
            new \DateTime('1981-01-03'),
            'male',
            self::ELECTED_REPRESENTATIVE_7_UUID
        );
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_10_UUID),
            MandateTypeEnum::CONSEILLER_TERRITORIAL,
            false,
            null,
            LaREMSupportEnum::INFORMAL,
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_94'),
            $erWithNotElectedMandate,
            false,
            new \DateTime('2020-03-15')
        );
        $erWithNotElectedMandate->addMandate($mandate);

        $manager->persist($erWithNotElectedMandate);

        // with mandate CITY_COUNCIL 75007
        $erParis = $this->createElectedRepresentative('Arrondissement', 'PARIS', new \DateTime('1972-02-02'), 'male');
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_11_UUID),
            MandateTypeEnum::CONSEILLER_MUNICIPAL,
            true,
            null,
            null,
            LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75107'),
            $erParis,
            true,
            new \DateTime('2019-03-15')
        );
        $erParis->addMandate($mandate);

        $manager->persist($erParis);

        // with mandate DEPUTY CIRCO 75
        $erParis2 = $this->createElectedRepresentative('Circonscription', 'PARISS', new \DateTime('1982-03-03'), 'female');
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_12_UUID),
            MandateTypeEnum::DEPUTE,
            true,
            null,
            LaREMSupportEnum::OFFICIAL,
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            $erParis2,
            true,
            new \DateTime('2019-01-11')
        );
        $erParis2->addMandate($mandate);

        $manager->persist($erParis2);

        // with mandate SENATOR 75
        $erParis3 = $this->createElectedRepresentative('Département', 'PARIS', new \DateTime('1962-04-04'), 'male');
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_13_UUID),
            MandateTypeEnum::SENATEUR,
            true,
            null,
            null,
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_75'),
            $erParis3,
            true,
            new \DateTime('2018-01-11')
        );
        $erParis3->addMandate($mandate);

        $manager->persist($erParis3);

        $erDepartment59 = $this->createElectedRepresentative('Département', 'Nord', new \DateTime('1962-04-04'), 'male');
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_14_UUID),
            MandateTypeEnum::SENATEUR,
            true,
            null,
            null,
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'),
            $erDepartment59,
            true,
            new \DateTime('2018-01-11')
        );
        $erDepartment59->addMandate($mandate);

        $manager->persist($erDepartment59);

        // with adherent and ongoing mandate
        $erDepartment92 = $this->createElectedRepresentative('Département', '92', new \DateTime('1982-03-03'), 'male', self::ELECTED_REPRESENTATIVE_8_UUID);
        $erDepartment92->setAdherent($this->getReference('renaissance-user-2', Adherent::class));
        $erDepartment92->setCreatedByAdherent($this->getReference('adherent-8', Adherent::class));
        $mandate = new Mandate(
            Uuid::fromString(self::ELECTED_MANDATE_15_UUID),
            MandateTypeEnum::SENATEUR,
            true,
            null,
            LaREMSupportEnum::OFFICIAL,
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            $erDepartment92,
            true,
            new \DateTime('2019-01-11')
        );
        $erDepartment92->addMandate($mandate);
        $this->addReference('elected-representative-dpt-92', $erDepartment92);

        $manager->persist($erDepartment92);

        $manager->flush();
    }

    private function createElectedRepresentative(
        string $firstName,
        string $lastName,
        \DateTime $birthDate,
        ?string $gender = null,
        ?string $uuid = null,
    ): ElectedRepresentative {
        return ElectedRepresentative::create(
            $firstName,
            $lastName,
            $birthDate,
            $gender,
            $uuid ? Uuid::fromString($uuid) : null
        );
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
