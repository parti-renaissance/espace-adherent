<?php

namespace App\Assessor;

use App\Entity\AssessorRequest;
use App\Entity\VotePlace;
use App\Utils\PhoneNumberUtils;
use Doctrine\ORM\EntityManagerInterface;
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
        return AssessorRequest::create(
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
            PhoneNumberUtils::create($data['phoneNumber']),
            $data['assessorCity'] ?? null,
            $data['assessorPostalCode'] ?? null,
            $data['birthName'],
            $data['office'],
            $data['enabled'] ?? true,
            $data['reachable'] ?? false,
            $data['assessorCountry'] ?? 'FR'
        );
    }

    public function createFromCommand(AssessorRequestCommand $assessorRequestCommand): AssessorRequest
    {
        return AssessorRequest::create(
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
            $assessorRequestCommand->getBirthName(),
            $assessorRequestCommand->getOffice(),
            true,
            $assessorRequestCommand->isReachable(),
            $assessorRequestCommand->getAssessorCountry(),
            $this->getVotePlaceWishesChoices($assessorRequestCommand->getVotePlaceWishes())
        );
    }

    private function getVotePlaceWishesChoices(array $ids): array
    {
        $references = [];

        foreach ($ids as $id) {
            $references[] = $this->manager->getPartialReference(VotePlace::class, $id);
        }

        return $references;
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
