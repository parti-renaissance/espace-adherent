<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     attributes={
 *         "access_control": "is_granted('ROLE_DATA_CORNER')",
 *         "normalization_context": {"groups": {"audience_read"}},
 *         "denormalization_context": {"groups": {"audience_write"}},
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/v3/audiences",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *         "delete": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *     }
 * )
 */
class Audience
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $name;

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
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_read", "audience_write"})
     *
     * @Assert\NotBlank
     */
    private $zone;

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
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $subscriptionType;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    private $hasSmsSubscription;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function isCommitteeMember(): ?bool
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

    public function hasEmailSubscription(): ?bool
    {
        return $this->hasEmailSubscription;
    }

    public function setHasEmailSubscription(?bool $hasEmailSubscription): void
    {
        $this->hasEmailSubscription = $hasEmailSubscription;
    }

    public function getSubscriptionType(): ?string
    {
        return $this->subscriptionType;
    }

    public function setSubscriptionType(?string $subscriptionType): void
    {
        $this->subscriptionType = $subscriptionType;
    }

    public function hasSmsSubscription(): ?bool
    {
        return $this->hasSmsSubscription;
    }

    public function setHasSmsSubscription(?bool $hasSmsSubscription): void
    {
        $this->hasSmsSubscription = $hasSmsSubscription;
    }
}
