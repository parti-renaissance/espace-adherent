<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Entity\AssessorRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadAssessorRequestData extends Fixture
{
    private const ASSESSOR_REQUEST_1_UUID = 'be61ba07-c8e7-4533-97e1-0ab215cd752c';
    private const ASSESSOR_REQUEST_2_UUID = '91b8d3a3-6757-4cf6-becc-5e3786bbbf5a';
    private const ASSESSOR_REQUEST_3_UUID = 'dac2c85f-4cff-4a09-a015-20168cfbe27c';
    private const ASSESSOR_REQUEST_4_UUID = 'a2087904-b05d-4bd6-b155-b3e486a25adf';
    private const ASSESSOR_REQUEST_5_UUID = '9e2e1fe6-9ff9-4b04-8fe7-1620c9df0e45';
    private const ASSESSOR_REQUEST_6_UUID = 'd320b698-10b7-4dd7-a70a-cedb95fceeda';
    private const ASSESSOR_REQUEST_7_UUID = 'f9286607-c3b5-4531-be03-81c3fb4fafe8';

    public function load(ObjectManager $manager)
    {
        $votePlaceLilleWazemmes = $this->getReference('vote-place-lille-wazemmes');
        $votePlaceLilleJeanZay = $this->getReference('vote-place-lille-jean-zay');
        $votePlaceBobigny = $this->getReference('vote-place-bobigny-blanqui');

        $manager->persist($unmatchedRequest1 = $this->createAssessorRequest(
            Uuid::fromString(self::ASSESSOR_REQUEST_1_UUID),
           'female',
           'Kepoura',
           'Adrienne',
           '14-05-1973',
           'Lille',
           '4 avenue du peuple Belge',
           '59000',
           'Lille',
           'Lille',
           '59350_0108',
           'adrienne.kepoura@example.fr',
           '0612345678',
           'Lille',
            '59350',
            AssessorOfficeEnum::SUBSTITUTE
        ));

        $manager->persist($matchedRequest1 = $this->createAssessorRequest(
            Uuid::fromString(self::ASSESSOR_REQUEST_2_UUID),
            'male',
            'Hytté',
            'Prosper',
            '10-07-1989',
            'Paris',
            '72 Rue du Faubourg Saint-Martin',
            '93008',
            'Paris',
            'Bobigny',
            '93008_0005',
            'prosper.hytte@example.fr',
            '0612345678',
            'Bobigny',
            '93008',
            AssessorOfficeEnum::SUBSTITUTE
        ));

        $manager->persist($matchedRequest2 = $this->createAssessorRequest(
            Uuid::fromString(self::ASSESSOR_REQUEST_3_UUID),
            'male',
            'Luc',
            'Ratif',
            '04-02-1992',
            'Paris',
            '70 Rue Saint-Martin',
            '93008',
            'Paris',
            'Bobigny',
            '93008_0005',
            'luc.ratif@example.fr',
            '0612345678',
            'Bobigny',
            '93008',
            AssessorOfficeEnum::HOLDER
        ));

        $manager->persist($matchedRequest3 = $this->createAssessorRequest(
            Uuid::fromString(self::ASSESSOR_REQUEST_4_UUID),
            'female',
            'Coptère',
            'Elise',
            '14-01-1986',
            'Lille',
            ' Pl. du Théâtre',
            '59000',
            'Lille',
            'Lille',
            '59350_0108',
            'elise.coptere@example.fr',
            '0612345678',
            'Lille',
            '59000',
            AssessorOfficeEnum::HOLDER
        ));

        $manager->persist($this->createAssessorRequest(
            Uuid::fromString(self::ASSESSOR_REQUEST_5_UUID),
            'male',
            'Sahalor',
            'Aubin',
            '12-08-1986',
            'Lille',
            ' Pl. du Théâtre',
            '59100',
            'Lille',
            'Lille',
            '59350_0108',
            'aubin.sahalor@example.fr',
            '0612345678',
            'Lille',
            '59100',
            AssessorOfficeEnum::SUBSTITUTE,
            null
        ));

        $manager->persist($requestOutOfManagedArea = $this->createAssessorRequest(
            Uuid::fromString(self::ASSESSOR_REQUEST_6_UUID),
            'male',
            'Parbal',
            'Gilles',
            '12-08-1986',
            'Angers',
            ' 4 rue Saint-Nicolas',
            '49000',
            'Angers',
            'Angers',
            '49000_0108',
            'gilles.parbal@example.fr',
            '0612345678',
            'Angers',
            '49000',
            AssessorOfficeEnum::HOLDER,
            null
        ));

        $manager->persist($foreignRequestOutOfManagedArea = $this->createAssessorRequest(
            Uuid::fromString(self::ASSESSOR_REQUEST_7_UUID),
            'male',
            'Cochet',
            'Henri',
            '12-10-1980',
            'London',
            ' 4 cover garden',
            null,
            'London',
            'London',
            '99999_0108',
            'henri.cochet@example.fr',
            '0612345678',
            'London',
            null,
            AssessorOfficeEnum::HOLDER,
            null,
            false,
            'UK'
        ));

        $unmatchedRequest1->addVotePlaceWish($votePlaceLilleWazemmes);
        $unmatchedRequest1->addVotePlaceWish($votePlaceLilleJeanZay);

        $matchedRequest1->addVotePlaceWish($votePlaceBobigny);
        $matchedRequest1->process($votePlaceBobigny);

        $matchedRequest2->addVotePlaceWish($votePlaceBobigny);
        $matchedRequest2->process($votePlaceBobigny);

        $matchedRequest3->addVotePlaceWish($votePlaceLilleWazemmes);
        $matchedRequest3->process($votePlaceLilleWazemmes);

        $manager->flush();
    }

    private function createAssessorRequest(
        UuidInterface $uuid,
        string $gender,
        string $lastName,
        string $firstName,
        string $birthDate,
        string $birthCity,
        string $address,
        ?string $postalCode,
        string $city,
        string $voteCity,
        string $officeNumber,
        string $emailAddress,
        string $phoneNumber,
        string $assessorCity,
        ?string $assessorPostalCode,
        string $office = AssessorOfficeEnum::SUBSTITUTE,
        string $birthName = null,
        bool $enabled = true,
        string $assessorCountry = 'FR'
    ): AssessorRequest {
        $assessor = new AssessorRequest();

        $assessor->setUuid($uuid);
        $assessor->setGender($gender);
        $assessor->setLastName($lastName);
        $assessor->setFirstName($firstName);
        $assessor->setBirthName($birthName);
        $assessor->setBirthdate(new \DateTime($birthDate));
        $assessor->setBirthCity($birthCity);
        $assessor->setAddress($address);
        $assessor->setPostalCode($postalCode);
        $assessor->setCity($city);
        $assessor->setVoteCity($voteCity);
        $assessor->setOfficeNumber($officeNumber);
        $assessor->setEmailAddress($emailAddress);
        $assessor->getPhone()->setNationalNumber($phoneNumber);
        $assessor->setAssessorCity($assessorCity);
        $assessor->setAssessorPostalCode($assessorPostalCode);
        $assessor->setAssessorCountry($assessorCountry);
        $assessor->setOffice($office);

        if (!$enabled) {
            $assessor->disable();
        }

        return $assessor;
    }

    public function getDependencies()
    {
        return [
            LoadVotePlaceData::class,
        ];
    }
}
