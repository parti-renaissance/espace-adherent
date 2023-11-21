<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="unregistrations")
 * @ORM\Entity(repositoryClass="App\Repository\UnregistrationRepository")
 */
class Unregistration
{
    public const REASON_EMAILS = 'unregistration_reasons.emails';
    public const REASON_TOOLS = 'unregistration_reasons.tools';
    public const REASON_SUPPORT = 'unregistration_reasons.support';
    public const REASON_GOVERNMENT = 'unregistration_reasons.government';
    public const REASON_ELECTED = 'unregistration_reasons.elected';
    public const REASON_MOVEMENT = 'unregistration_reasons.movement';
    public const REASON_COMMITTEE = 'unregistration_reasons.committee';
    public const REASON_OTHER = 'unregistration_reasons.other';

    public const REASONS_LIST_ADHERENT = [
        self::REASON_EMAILS,
        self::REASON_SUPPORT,
        self::REASON_GOVERNMENT,
        self::REASON_ELECTED,
        self::REASON_MOVEMENT,
        self::REASON_COMMITTEE,
        self::REASON_OTHER,
    ];

    public const REASONS_LIST_USER = [
        self::REASON_EMAILS,
        self::REASON_TOOLS,
        self::REASON_GOVERNMENT,
        self::REASON_ELECTED,
        self::REASON_MOVEMENT,
        self::REASON_OTHER,
    ];

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="uuid")
     */
    public ?UuidInterface $adherentUuid = null;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    #[Assert\NotBlank(message: 'adherent.unregistration.reasons')]
    private $reasons;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    #[Assert\Length(min: 10, max: 1000)]
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registeredAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $unregisteredAt;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdherent;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isRenaissance;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     */
    private $referentTags;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     */
    private $excludedBy;

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $adherentUuid,
        array $reasons,
        ?string $comment,
        \DateTime $registeredAt,
        ?string $postalCode,
        bool $isAdherent,
        bool $isRenaissance,
        array $referentTags,
        Administrator $admin = null
    ) {
        $this->uuid = $uuid;
        $this->adherentUuid = $adherentUuid;
        $this->postalCode = $postalCode;
        $this->reasons = $reasons;
        $this->comment = $comment;
        $this->registeredAt = $registeredAt;
        $this->unregisteredAt = new \DateTime('now');
        $this->isAdherent = $isAdherent;
        $this->isRenaissance = $isRenaissance;
        $this->referentTags = new ArrayCollection($referentTags);
        $this->excludedBy = $admin;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function getReasonsAsJson(): string
    {
        return \GuzzleHttp\json_encode($this->reasons, \JSON_PRETTY_PRINT);
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function getUnregisteredAt(): ?\DateTime
    {
        return $this->unregisteredAt;
    }

    public function isAdherent(): bool
    {
        return $this->isAdherent;
    }

    public function isRenaissance(): bool
    {
        return $this->isRenaissance;
    }

    /**
     * @return ReferentTag[]|Collection
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function getExcludedBy(): ?Administrator
    {
        return $this->excludedBy;
    }
}
