<?php

namespace App\Entity\Audience;

use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait AudienceFieldsTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=50)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=50)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $gender;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $ageMin;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $ageMax;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $registeredSince;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $registeredUntil;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $isCommitteeMember;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $isCertified;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $hasEmailSubscription;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $hasSmsSubscription;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"audience_write"})
     */
    private $scope;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $zone;

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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getAgeMin(): ?int
    {
        return $this->ageMin;
    }

    public function setAgeMin(?int $ageMin): void
    {
        $this->ageMin = $ageMin;
    }

    public function getAgeMax(): ?int
    {
        return $this->ageMax;
    }

    public function setAgeMax(?int $ageMax): void
    {
        $this->ageMax = $ageMax;
    }

    public function getRegisteredSince(): ?\DateTime
    {
        return $this->registeredSince;
    }

    public function setRegisteredSince(?\DateTime $registeredSince): void
    {
        $this->registeredSince = $registeredSince;
    }

    public function getRegisteredUntil(): ?\DateTime
    {
        return $this->registeredUntil;
    }

    public function setRegisteredUntil(?\DateTime $registeredUntil): void
    {
        $this->registeredUntil = $registeredUntil;
    }

    public function getIsCommitteeMember(): ?bool
    {
        return $this->isCommitteeMember;
    }

    public function setIsCommitteeMember(?bool $value): void
    {
        $this->isCommitteeMember = $value;
    }

    public function getIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(?bool $isCertified): void
    {
        $this->isCertified = $isCertified;
    }

    public function getHasEmailSubscription(): ?bool
    {
        return $this->hasEmailSubscription;
    }

    public function setHasEmailSubscription(?bool $hasEmailSubscription): void
    {
        $this->hasEmailSubscription = $hasEmailSubscription;
    }

    public function getHasSmsSubscription(): ?bool
    {
        return $this->hasSmsSubscription;
    }

    public function setHasSmsSubscription(?bool $hasSmsSubscription): void
    {
        $this->hasSmsSubscription = $hasSmsSubscription;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function toArray(): array
    {
        return array_filter([
            'gender' => $this->gender,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'age_min' => $this->ageMin,
            'age_max' => $this->ageMax,
            'registered_since' => $this->registeredSince,
            'registered_until' => $this->registeredUntil,
            'has_sms_subscription' => $this->hasSmsSubscription,
            'has_email_subscription' => $this->hasEmailSubscription,
            'is_committee_member' => $this->isCommitteeMember,
            'is_certified' => $this->isCertified,
            'scope' => $this->scope,
            'zones' => array_map(function (Zone $zone): string {
                return $zone->getUuid()->toString();
            }, $this->zones->toArray()),
        ]);
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }
}
