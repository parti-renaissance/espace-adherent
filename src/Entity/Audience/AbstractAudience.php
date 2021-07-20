<?php

namespace App\Entity\Audience;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityIdentityTrait;
use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="audience")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "referent": "App\Entity\Audience\ReferentAudience",
 *     "deputy": "App\Entity\Audience\DeputyAudience",
 *     "senator": "App\Entity\Audience\SenatorAudience",
 *     "candidate": "App\Entity\Audience\CandidateAudience",
 * })
 *
 * @ApiResource(
 *     attributes={
 *         "access_control": "is_granted('ROLE_DATA_CORNER')",
 *         "normalization_context": {"groups": {"audience_read"}},
 *         "denormalization_context": {"groups": {"audience_write"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/audiences",
 *             "controller": "App\Controller\Api\Audience\RetrieveAudiencesController",
 *             "normalization_context": {
 *                 "groups": {"audience_list_read"}
 *             },
 *         },
 *         "post": {
 *             "path": "/v3/audiences",
 *             "access_control": "is_granted('ROLE_DATA_CORNER') and is_granted('CAN_CREATE_AUDIENCE', object)",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('CAN_MANAGE_AUDIENCE', object)",
 *         },
 *         "put": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_DATA_CORNER') and is_granted('CAN_MANAGE_AUDIENCE', object)",
 *         },
 *         "delete": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_DATA_CORNER') and is_granted('CAN_MANAGE_AUDIENCE', object)",
 *         },
 *     }
 * )
 */
abstract class AbstractAudience
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     *
     * @Groups({"audience_read", "audience_write", "audience_list_read"})
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=50)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=50)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $lastName;

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
    protected $gender;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $ageMin;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $ageMax;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $registeredSince;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $registeredUntil;

    /**
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_read", "audience_write"})
     *
     * @Assert\NotBlank
     */
    protected $zone;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $isCommitteeMember;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $isCertified;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $hasEmailSubscription;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_read", "audience_write"})
     */
    protected $hasSmsSubscription;

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

    public function hasSmsSubscription(): ?bool
    {
        return $this->hasSmsSubscription;
    }

    public function setHasSmsSubscription(?bool $hasSmsSubscription): void
    {
        $this->hasSmsSubscription = $hasSmsSubscription;
    }
}
