<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectSkill;
use AppBundle\Entity\CitizenProjectCommitteeSupport;
use AppBundle\Entity\Committee;
use AppBundle\Validator\UniqueCitizenProject as AssertUniqueCitizenProject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    public $phone;

    public $category;

    public $committeeSupports;

    private $committees;

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

    public $skills;

    public function __construct(NullableAddress $address = null)
    {
        $this->address = $address;
        $this->skills = new ArrayCollection();
        $this->committeeSupports = new ArrayCollection();
        $this->committees = new ArrayCollection();
    }

    public static function createFromCitizenProject(CitizenProject $citizenProject): self
    {
        $address = $citizenProject->getPostAddress() ? NullableAddress::createFromAddress($citizenProject->getPostAddress()) : null;
        $dto = new self($address);
        $dto->name = $citizenProject->getName();
        $dto->subtitle = $citizenProject->getSubtitle();
        $dto->category = $citizenProject->getCategory();
        $dto->phone = $citizenProject->getPhone();
        $dto->committeeSupports = $citizenProject->getCommitteeSupports();
        $dto->problemDescription = $citizenProject->getProblemDescription();
        $dto->proposedSolution = $citizenProject->getProposedSolution();
        $dto->requiredMeans = $citizenProject->getRequiredMeans();
        $dto->assistanceNeeded = $citizenProject->isAssistanceNeeded();
        $dto->assistanceContent = $citizenProject->getAssistanceContent();
        $dto->citizenProject = $citizenProject;
        $dto->skills = $citizenProject->getSkills();

        /** @var CitizenProjectCommitteeSupport $committeeSupport */
        foreach ($dto->committeeSupports as $committeeSupport) {
            $dto->addCommittee($committeeSupport->getCommittee());
        }

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

    public function getCommitteeSupports(): Collection
    {
        return $this->committeeSupports;
    }

    public function setCommitteeSupports(Collection $committeeSupports): void
    {
        $this->committeeSupports = $committeeSupports;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function isAssistanceNeeded(): bool
    {
        return $this->assistanceNeeded;
    }

    public function getAssistanceContent(): ?string
    {
        return $this->assistanceContent;
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

    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function setSkills(iterable $skills): void
    {
        $this->skills->clear();
        foreach ($skills as $skill) {
            $this->addSkill($skill);
        }
    }

    public function addSkill(CitizenProjectSkill $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }
    }

    public function getCommittees(): Collection
    {
        return $this->committees;
    }

    public function setCommittees(iterable $committees): void
    {
        $this->committees = $committees;
    }

    public function addCommittee(Committee $committee): void
    {
        if (!$this->committees->contains($committee)) {
            $this->committees->add($committee);
        }
    }
}
