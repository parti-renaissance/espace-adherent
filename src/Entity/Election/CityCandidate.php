<?php

namespace AppBundle\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_city_candidate")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CityCandidate
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\Choice(choices=AppBundle\ValueObject\Genders::CHOICES, strict=true)
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $email;

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $politicalScheme;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $alliances;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $agreement;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    private $eligibleAdvisersCount;

    public function __construct(
        string $firstName = null,
        string $lastName = null,
        ?string $gender = null,
        ?string $email = null,
        ?PhoneNumber $phone = null,
        ?string $politicalScheme = null,
        ?string $alliances = null,
        ?bool $agreement = false,
        ?int $eligibleAdvisersCount = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->gender = $gender;
        $this->email = $email;
        $this->phone = $phone;
        $this->politicalScheme = $politicalScheme;
        $this->alliances = $alliances;
        $this->agreement = $agreement;
        $this->eligibleAdvisersCount = $eligibleAdvisersCount;
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getPoliticalScheme(): ?string
    {
        return $this->politicalScheme;
    }

    public function setPoliticalScheme(?string $politicalScheme): void
    {
        $this->politicalScheme = $politicalScheme;
    }

    public function getAlliances(): ?string
    {
        return $this->alliances;
    }

    public function setAlliances(?string $alliances): void
    {
        $this->alliances = $alliances;
    }

    public function hasAgreement(): bool
    {
        return $this->agreement;
    }

    public function setAgreement(bool $agreement): void
    {
        $this->agreement = $agreement;
    }

    public function getEligibleAdvisersCount(): ?int
    {
        return $this->eligibleAdvisersCount;
    }

    public function setEligibleAdvisersCount(?int $eligibleAdvisersCount): void
    {
        $this->eligibleAdvisersCount = $eligibleAdvisersCount;
    }

    public function isEmpty(): bool
    {
        return !$this->firstName && !$this->lastName;
    }
}
