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
        foreach (['name', 'description', 'created_by'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $phone = null;
        if (isset($data['phone'])) {
            $phone = $this->createPhone($data['phone']);
        }

        $uuid = isset($data['uuid'])
            ? Uuid::fromString($data['uuid'])
            : CitizenProject::createUuid($data['name']);

        $citizenProject = CitizenProject::createSimple(
            $uuid,
            $data['created_by'],
            $data['name'],
            $data['description'],
            $data['address'] ?? null,
            $phone,
            $data['created_at'] ?? 'now'
        );

        if (isset($data['slug'])) {
            $citizenProject->updateSlug($data['slug']);
        }

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
            $command->description,
            $command->getAddress() ? $this->addressFactory->createFromNullableAddress($command->getAddress()) : null,
            $command->getPhone()
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
