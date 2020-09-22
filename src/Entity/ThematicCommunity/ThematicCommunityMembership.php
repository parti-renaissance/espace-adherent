<?php

namespace App\Entity\ThematicCommunity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityUserListDefinitionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "contact": "App\Entity\ThematicCommunity\ContactMembership",
 *     "adherent": "App\Entity\ThematicCommunity\AdherentMembership",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class ThematicCommunityMembership
{
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

    use EntityIdentityTrait;
    use EntityUserListDefinitionTrait;

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
     */
    private $association = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $associationName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $motivation;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $expert = false;

    /**
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $adherent;

    /**
     * @var Contact
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ThematicCommunity\Contact", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $contact;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
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

    public function getMotivation(): ?string
    {
        return $this->motivation;
    }

    public function setMotivation(string $motivation): void
    {
        $this->motivation = $motivation;
    }

    public function isExpert(): bool
    {
        return $this->expert;
    }

    public function setExpert(bool $expert): void
    {
        $this->expert = $expert;
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
}
