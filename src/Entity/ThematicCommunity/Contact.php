<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'thematic_community_contact')]
#[ORM\Entity]
class Contact
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column]
    private $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column]
    private $lastName;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Email
     */
    #[ORM\Column]
    private $email;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $gender;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $customGender;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $birthDate;

    /**
     * @var PhoneNumber
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $phone;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(nullable: true)]
    private $activityArea;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(nullable: true)]
    private $jobArea;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(nullable: true)]
    private $job;

    #[ORM\Column(nullable: true)]
    private $position;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->postAddress = PostAddress::createEmptyAddress();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getCustomGender(): ?string
    {
        return $this->customGender;
    }

    public function setCustomGender(?string $customGender): void
    {
        $this->customGender = $customGender;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getActivityArea(): ?string
    {
        return $this->activityArea;
    }

    public function setActivityArea(string $activityArea): void
    {
        $this->activityArea = $activityArea;
    }

    public function getJobArea(): ?string
    {
        return $this->jobArea;
    }

    public function setJobArea(string $jobArea): void
    {
        $this->jobArea = $jobArea;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(string $job): void
    {
        $this->job = $job;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        if (!ActivityPositionsEnum::exists($position)) {
            throw new \InvalidArgumentException(sprintf('Invalid position "%s", known positions are "%s".', $position, implode('", "', ActivityPositionsEnum::ALL)));
        }

        $this->position = $position;
    }
}
