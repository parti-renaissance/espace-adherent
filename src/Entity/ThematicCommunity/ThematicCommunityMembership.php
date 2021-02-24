<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityUserListDefinitionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThematicCommunity\ThematicCommunityMembershipRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "contact": "App\Entity\ThematicCommunity\ContactMembership",
 *     "adherent": "App\Entity\ThematicCommunity\AdherentMembership",
 * })
 */
abstract class ThematicCommunityMembership
{
    use EntityIdentityTrait;
    use EntityUserListDefinitionTrait;

    public const TYPE_ADHERENT = 'adherent';
    public const TYPE_ELECTED_REPRESENTATIVE = 'electedRepresentative';
    public const TYPE_CONTACT = 'contact';

    public const TYPES = [
        self::TYPE_ADHERENT,
        self::TYPE_ELECTED_REPRESENTATIVE,
        self::TYPE_CONTACT,
    ];

    public const MOTIVATION_THINKING = 'thinking';
    public const MOTIVATION_INFORMATION = 'information';
    public const MOTIVATION_ON_SPOT = 'on_spot';

    public const MOTIVATIONS = [
        self::MOTIVATION_THINKING,
        self::MOTIVATION_INFORMATION,
        self::MOTIVATION_ON_SPOT,
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';

    /**
     * @var ThematicCommunity
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ThematicCommunity\ThematicCommunity")
     */
    protected $community;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $joinedAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Assert\NotNull
     */
    private $hasJob = false;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $job;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Assert\NotNull
     */
    private $association = false;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $associationName;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\NotBlank
     */
    private $motivations = [];

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $expert = false;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var Adherent|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $adherent;

    /**
     * @var Contact|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ThematicCommunity\Contact", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $contact;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->joinedAt = new \DateTime();
        $this->userListDefinitions = new ArrayCollection();
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTime $joinedAt): void
    {
        $this->joinedAt = $joinedAt;
    }

    public function getCommunity(): ThematicCommunity
    {
        return $this->community;
    }

    public function setCommunity(ThematicCommunity $community): void
    {
        $this->community = $community;
    }

    public function hasJob(): bool
    {
        return $this->hasJob;
    }

    public function setHasJob(bool $hasJob): void
    {
        $this->hasJob = $hasJob;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(?string $job): void
    {
        $this->job = $job;
    }

    public function isAssociation(): bool
    {
        return $this->association;
    }

    public function setAssociation(bool $association): void
    {
        $this->association = $association;
    }

    public function getAssociationName(): ?string
    {
        return $this->associationName;
    }

    public function setAssociationName(string $associationName): void
    {
        $this->associationName = $associationName;
    }

    public function getMotivations(): array
    {
        return $this->motivations;
    }

    public function setMotivations(array $motivations): void
    {
        $this->motivations = $motivations;
    }

    public function isExpert(): bool
    {
        return $this->expert;
    }

    public function setExpert(bool $expert): void
    {
        $this->expert = $expert;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isPending(): bool
    {
        return self::STATUS_PENDING === $this->status;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): void
    {
        $this->contact = $contact;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getType(): ?string
    {
        return [
            AdherentMembership::class => self::TYPE_ADHERENT,
            ContactMembership::class => self::TYPE_CONTACT,
        ][static::class] ?? null;
    }

    abstract public function getCityName(): ?string;

    abstract public function getPostalCode(): ?string;

    public function getCityWithZipcode(): string
    {
        if (null === $city = $this->getCityName()) {
            return '';
        }

        if (null === $zipcode = $this->getPostalCode()) {
            return $city;
        }

        return sprintf('%s (%s)', $city, $zipcode);
    }

    public function getRoles(): string
    {
        if ($this->contact) {
            return 'Contact';
        }

        $roles = [];

        if ($this->adherent->isReferent()) {
            $roles[] = 'Référent';
        }

        if ($this->adherent->isSupervisor()) {
            $roles[] = 'Animateur local';
        }

        if ($this->adherent->isCitizenProjectAdministrator()) {
            $roles[] = 'Porteur de projets citoyens';
        }

        if (!empty($roles)) {
            return \implode(' / ', $roles);
        }

        return 'Adhérent';
    }
}
