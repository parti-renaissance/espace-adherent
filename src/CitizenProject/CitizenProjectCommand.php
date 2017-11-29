<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\Committee;
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
     * @Assert\Length(min=5, max=60)
     */
    public $subtitle;

    /**
     * The citizen project address.
     *
     * @var NullableAddress
     *
     * @Assert\NotBlank
     * @Assert\Valid
     */
    public $address;

    /**
     * @Assert\NotBlank(message="common.phone_number.required")
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    protected $phone;

    public $category;

    private $committee;

    public $assistanceNeeded = false;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=500)
     */
    public $problemDescription;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=800)
     */
    public $proposedSolution;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=800)
     */
    public $requiredMeans;

    /**
     * @var string
     */
    public $assistanceContent;

    public function __construct(NullableAddress $address = null)
    {
        $this->address = $address;
    }

    public static function createFromCitizenProject(CitizenProject $citizenProject): self
    {
        $address = $citizenProject->getPostAddress() ? NullableAddress::createFromAddress($citizenProject->getPostAddress()) : null;
        $dto = new self($address);
        $dto->name = $citizenProject->getName();
        $dto->subtitle = $citizenProject->getSubtitle();
        $dto->category = $citizenProject->getCategory();
        $dto->phone = $citizenProject->getPhone();
        $dto->committee = $citizenProject->getCommittee();
        $dto->problemDescription = $citizenProject->getProblemDescription();
        $dto->proposedSolution = $citizenProject->getProposedSolution();
        $dto->requiredMeans = $citizenProject->getRequiredMeans();
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

    public function getCategory(): ?CitizenProjectCategory
    {
        return $this->category;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function isAssistanceNeeded(): bool
    {
        return $this->assistanceNeeded;
    }

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function getProposedSolution(): ?string
    {
        return $this->proposedSolution;
    }

    public function getRequiredMeans(): ?string
    {
        return $this->requiredMeans;
    }

    public function setCitizenProject(CitizenProject $citizenProject): void
    {
        $this->citizenProject = $citizenProject;
    }
}
