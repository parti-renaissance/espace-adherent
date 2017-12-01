<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\CitizenProject;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;

class CitizenProjectFactory
{
    private $addressFactory;

    public function __construct(PostAddressFactory $addressFactory = null)
    {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromArray(array $data): CitizenProject
    {
        foreach (['uuid', 'name', 'subtitle', 'category', 'address', 'problem_description', 'proposed_solution', 'required_means', 'created_by'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = isset($data['uuid'])
            ? Uuid::fromString($data['uuid'])
            : CitizenProject::createUuid($data['name']);

        $phone = null;
        if (isset($data['phone'])) {
            $phone = $this->createPhone($data['phone']);
        }

        $citizenProject = new CitizenProject(
            $uuid,
            Uuid::fromString($data['created_by']),
            $data['name'],
            $data['subtitle'],
            $data['category'],
            $data['committee'] ?? null,
            $data['assistance_needed'] ?? false,
            $data['assistance_content'] ?? null,
            $data['problem_description'],
            $data['proposed_solution'],
            $data['required_means'],
            $phone,
            $data['address'],
            $data['slug'] ?? null,
            $data['status'] ?? CitizenProject::PENDING
        );

        return $citizenProject;
    }

    /**
     * Returns a new instance of CitizenProject from a CreateCitizenProjectCommand DTO.
     *
     * @param CitizenProjectCreationCommand $command
     *
     * @return CitizenProject
     */
    public function createFromCitizenProjectCreationCommand(CitizenProjectCreationCommand $command): CitizenProject
    {
        $citizenProject = CitizenProject::createForAdherent(
            $command->getAdherent(),
            $command->name,
            $command->subtitle,
            $command->category,
            $command->getPhone(),
            $command->assistanceNeeded,
            $command->assistanceContent,
            $command->problemDescription,
            $command->proposedSolution,
            $command->requiredMeans,
            $command->getCommittee(),
            $command->getAddress() ? $this->addressFactory->createFromNullableAddress($command->getAddress()) : null
        );

        return $citizenProject;
    }

    /**
     * Returns a PhoneNumber object.
     *
     * The format must be something like "33 0102030405"
     *
     * @param string $phoneNumber
     *
     * @return PhoneNumber
     */
    private function createPhone($phoneNumber): PhoneNumber
    {
        list($country, $number) = explode(' ', $phoneNumber);

        $phone = new PhoneNumber();
        $phone->setCountryCode($country);
        $phone->setNationalNumber($number);

        return $phone;
    }
}
