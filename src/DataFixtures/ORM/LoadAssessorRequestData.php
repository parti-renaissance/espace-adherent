<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Entity\AssessorRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAssessorRequestData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $votePlaceLilleWazemmes = $this->getReference('vote-place-lille-wazemmes');
        $votePlaceLilleJeanZay = $this->getReference('vote-place-lille-jean-zay');

        $manager->persist($request1 = $this->createAssessorRequest(
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
            AssessorOfficeEnum::HOLDER
        ));

        $manager->persist($request2 = $this->createAssessorRequest(
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
            AssessorOfficeEnum::HOLDER
        ));

        $request1->addVotePlaceWish($votePlaceLilleWazemmes);
        $request1->addVotePlaceWish($votePlaceLilleJeanZay);
        $request2->addVotePlaceWish($votePlaceLilleJeanZay);

        $votePlaceLilleWazemmes->addAssessorRequest($request1);
        $votePlaceLilleJeanZay->addAssessorRequest($request2);

        $manager->flush();
    }

    private function createAssessorRequest(
        string $gender,
        string $lastName,
        string $firstName,
        string $birthDate,
        string $birthCity,
        string $address,
        string $postalCode,
        string $city,
        string $voteCity,
        string $officeNumber,
        string $emailAddress,
        string $phoneNumber,
        string $assessorCity,
        string $office = AssessorOfficeEnum::SUBSTITUTE,
        string $birthName = null
    ): AssessorRequest {
        $assessor = new AssessorRequest();

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
        $assessor->setOffice($office);

        return $assessor;
    }

    public function getDependencies()
    {
        return [
            LoadVotePlaceData::class,
        ];
    }
}
