<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"code"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class VotePlace
{
    use EntityTimestampableTrait;

    public const MAX_ASSESSOR_REQUESTS = 2;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(length=10, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=10)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(max=50)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country = 'FR';

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AssessorRequest", mappedBy="votePlace")
     *
     * @Assert\Choice(
     *     max=VotePlace::MAX_ASSESSOR_REQUESTS,
     *     maxMessage="vote_place.assessor_request.max"
     * )
     */
    private $assessorRequests;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $holderOfficeAvailable = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $substituteOfficeAvailable = true;

    public function __construct()
    {
        $this->assessorRequests = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getAssessorRequests(): Collection
    {
        return $this->assessorRequests;
    }

    public function addAssessorRequest(AssessorRequest $assessorRequests): void
    {
        if (!$this->assessorRequests->contains($assessorRequests)) {
            $this->assessorRequests->add($assessorRequests);
        }
    }

    public function removeAssessorRequest(AssessorRequest $assessorRequests): void
    {
        $this->assessorRequests->removeElement($assessorRequests);
    }

    public function getAvailableOffices(): array
    {
        $availableOffices = AssessorOfficeEnum::CHOICES;

        foreach ($this->assessorRequests as $assessorRequest) {
            if (\in_array($assessorRequest->getOffice(), $availableOffices)) {
                unset($availableOffices[array_search($assessorRequest->getOffice(), $availableOffices)]);
            }
        }

        return array_keys($availableOffices);
    }

    public function isHolderOfficeAvailable(): bool
    {
        return $this->holderOfficeAvailable;
    }

    public function setHolderOfficeAvailable(bool $holderOfficeAvailable): void
    {
        $this->holderOfficeAvailable = $holderOfficeAvailable;
    }

    public function isSubstituteOfficeAvailable(): bool
    {
        return $this->substituteOfficeAvailable;
    }

    public function setSubstituteOfficeAvailable(bool $substituteOfficeAvailable): void
    {
        $this->substituteOfficeAvailable = $substituteOfficeAvailable;
    }
}
