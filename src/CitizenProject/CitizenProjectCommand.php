<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectSkill;
use AppBundle\Entity\Committee;
use AppBundle\Validator\WysiwygLength as AssertWysiwygLength;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\Length(min=5, max=80)
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
     * @var string
     *
     * @Assert\Length(max=50)
     */
    public $district;

    /**
     * @Assert\NotBlank(message="common.phone_number.required")
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    public $phone;

    /**
     * @Assert\NotNull
     * @Assert\Type("AppBundle\Entity\CitizenProjectCategory")
     */
    public $category;

    public $committeeSupports;

    public $removeImage = false;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=500)
     */
    public $problemDescription;

    /**
     * @Assert\NotBlank
     * @AssertWysiwygLength(max=800)
     */
    public $proposedSolution;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=800)
     */
    public $requiredMeans;

    public $skills;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png"},
     *     minWidth="1200",
     *     minHeight="675",
     *     minRatio=1.77,
     * )
     */
    private $image;

    public function __construct(NullableAddress $address = null)
    {
        $this->address = $address;
        $this->skills = new ArrayCollection();
        $this->committeeSupports = new ArrayCollection();
        $this->committees = new ArrayCollection();
    }

    public function getCityName(): string
    {
        return $this->address->getCityName();
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): void
    {
        $this->district = $district;
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

    public function isCitizenProjectApproved(): bool
    {
        return $this->citizenProject && $this->citizenProject->isApproved();
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function setImage(?UploadedFile $image): void
    {
        $this->image = $image;
    }

    public function isRemoveImage(): bool
    {
        return $this->removeImage;
    }
}
