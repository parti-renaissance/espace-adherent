<?php

namespace App\Entity;

use App\Adherent\Unregistration\TypeEnum;
use App\Repository\UnregistrationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UnregistrationRepository::class)]
#[ORM\Table(name: 'unregistrations')]
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
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Id]
    private $id;

    /**
     * @var UuidInterface
     */
    #[ORM\Column(type: 'uuid')]
    private $uuid;

    #[ORM\Column(type: 'uuid')]
    public ?UuidInterface $adherentUuid = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 15, nullable: true)]
    private $postalCode;

    #[ORM\Column(nullable: true, enumType: TypeEnum::class)]
    private ?TypeEnum $type;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $tags;

    #[Assert\NotBlank(message: 'adherent.unregistration.reasons')]
    #[ORM\Column(type: 'json', nullable: true)]
    private $reasons;

    /**
     * @var string
     */
    #[Assert\Length(min: 10, max: 1000)]
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    #[ORM\Column(type: 'datetime')]
    private $registeredAt;

    #[ORM\Column(type: 'datetime')]
    private $unregisteredAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isAdherent;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isRenaissance;

    /**
     * @var string
     */
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $excludedBy;

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $adherentUuid,
        array $reasons,
        ?string $comment,
        \DateTime $registeredAt,
        ?string $postalCode,
        ?TypeEnum $type,
        ?array $tags,
        bool $isAdherent,
        bool $isRenaissance,
        ?Administrator $admin = null,
    ) {
        $this->uuid = $uuid;
        $this->adherentUuid = $adherentUuid;
        $this->postalCode = $postalCode;
        $this->type = $type;
        $this->tags = $tags;
        $this->reasons = $reasons;
        $this->comment = $comment;
        $this->registeredAt = $registeredAt;
        $this->unregisteredAt = new \DateTime('now');
        $this->isAdherent = $isAdherent;
        $this->isRenaissance = $isRenaissance;
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

    public function getType(): ?TypeEnum
    {
        return $this->type;
    }

    public function getTags(): array
    {
        return $this->tags;
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

    public function getExcludedBy(): ?Administrator
    {
        return $this->excludedBy;
    }
}
