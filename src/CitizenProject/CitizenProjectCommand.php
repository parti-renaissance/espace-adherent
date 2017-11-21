<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\CitizenProject;
use AppBundle\Validator\UniqueCitizenProject as AssertUniqueCitizenProject;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueCitizenProject
 */
class CitizenProjectCommand
{
    /** @var CitizenProject */
    protected $citizenProject;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=60)
     */
    public $name;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=140, minMessage="citizen_project.description.min_length", maxMessage="citizen_project.description.max_length")
     */
    public $description;

    /**
     * The citizen project address.
     *
     * @var NullableAddress
     *
     * @Assert\Valid
     */
    protected $address;

    /**
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    protected $phone;

    protected function __construct(NullableAddress $address = null)
    {
        $this->address = $address;
    }

    public static function createFromCitizenProject(CitizenProject $citizenProject): self
    {
        $address = $citizenProject->getPostAddress() ? NullableAddress::createFromAddress($citizenProject->getPostAddress()) : null;
        $dto = new self($address);
        $dto->name = $citizenProject->getName();
        $dto->description = $citizenProject->getDescription();
        $dto->phone = $citizenProject->getPhone();
        $dto->citizenProject = $citizenProject;

        return $dto;
    }

    public function getCityName(): string
    {
        return $this->address->getCityName();
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getCitizenProject(): ?CitizenProject
    {
        return $this->citizenProject;
    }

    public function getCitizenProjectUuid(): UuidInterface
    {
        return $this->citizenProject->getUuid();
    }

    public function getCitizenProjectSlug(): string
    {
        return $this->citizenProject->getSlug();
    }

    public function setAddress(NullableAddress $address = null): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?NullableAddress
    {
        return $this->address;
    }
}
