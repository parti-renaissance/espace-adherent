<?php

namespace AppBundle\Assessor;

use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;

class AssessorRequestFactory
{
    /** @var EntityManagerInterface */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public static function createFromArray(array $data): AssessorRequest
    {
        $assessor = AssessorRequest::create(
            $data['uuid'],
            $data['gender'],
            $data['lastName'],
            $data['firstName'],
            self::createBirthdate($data['birthdate']),
            $data['birthCity'],
            $data['address'],
            $data['postalCode'],
            $data['city'],
            $data['voteCity'],
            $data['officeNumber'],
            $data['emailAddress'],
            self::createPhone($data['phoneNumber']),
            $data['assessorCity'] ?? null,
            $data['assessorPostalCode'] ?? null,
            $data['office'],
            $data['birthName'] ?? null,
            $data['enabled'] ?? true,
            $data['reachable'] ?? false,
            $data['assessorCountry'] ?? 'FR'
        );

        return $assessor;
    }

    public function createFromCommand(AssessorRequestCommand $assessorRequestCommand): AssessorRequest
    {
        $assessorRequest = AssessorRequest::create(
            Uuid::uuid4(),
            $assessorRequestCommand->getGender(),
            $assessorRequestCommand->getLastName(),
            $assessorRequestCommand->getFirstName(),
            $assessorRequestCommand->getBirthdate(),
            $assessorRequestCommand->getBirthCity(),
            $assessorRequestCommand->getAddress(),
            $assessorRequestCommand->getPostalCode(),
            $assessorRequestCommand->getCity(),
            $assessorRequestCommand->getVoteCity(),
            $assessorRequestCommand->getOfficeNumber(),
            $assessorRequestCommand->getEmailAddress(),
            $assessorRequestCommand->getPhone(),
            $assessorRequestCommand->getAssessorCity(),
            $assessorRequestCommand->getAssessorPostalCode(),
            $assessorRequestCommand->getOffice(),
            $assessorRequestCommand->getBirthName(),
            true,
            $assessorRequestCommand->isReachable(),
            $assessorRequestCommand->getAssessorCountry(),
            $this->getVotePlaceWishesChoices($assessorRequestCommand->getVotePlaceWishes())
        );

        return $assessorRequest;
    }

    private function getVotePlaceWishesChoices(array $ids): array
    {
        $references = [];

        foreach ($ids as $id) {
            $references[] = $this->manager->getPartialReference(VotePlace::class, $id);
        }

        return $references;
    }

    private static function createPhone(string $phoneNumber): PhoneNumber
    {
        list($country, $number) = explode(' ', $phoneNumber);

        $phone = new PhoneNumber();
        $phone->setCountryCode($country);
        $phone->setNationalNumber($number);

        return $phone;
    }

    /**
     * @param int|string|\DateTime $birthdate Valid date reprensentation
     */
    private static function createBirthdate($birthdate): \DateTime
    {
        if ($birthdate instanceof \DateTime) {
            return $birthdate;
        }

        return new \DateTime($birthdate);
    }
}
