<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class TerritorialCouncilMembershipLog
{
    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';

    public const ALL_TYPES = [
        self::TYPE_INFO,
        self::TYPE_WARNING,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(length=20)
     */
    private $type;

    /**
     * @ORM\Column(length=255)
     */
    private $description;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @ORM\Column(length=50)
     */
    private $qualityName;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    private $actualTerritorialCouncil;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $actualQualityNames = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $foundTerritorialCouncils = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isResolved = false;

    public function __construct(
        string $type = null,
        string $description = null,
        Adherent $adherent = null,
        string $qualityName = null,
        string $territorialCouncil = null,
        array $qualityNames = [],
        array $territorialCouncils = []
    ) {
        $this->type = $type;
        $this->adherent = $adherent;
        $this->qualityName = $qualityName;
        $this->actualTerritorialCouncil = $territorialCouncil;
        $this->actualQualityNames = $qualityNames;
        $this->foundTerritorialCouncils = $territorialCouncils;
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getQualityName(): string
    {
        return $this->qualityName;
    }

    public function setQualityName(string $qualityName): void
    {
        $this->qualityName = $qualityName;
    }

    public function getActualTerritorialCouncil(): ?string
    {
        return $this->actualTerritorialCouncil;
    }

    public function setActualTerritorialCouncil(?string $actualTerritorialCouncil): void
    {
        $this->actualTerritorialCouncil = $actualTerritorialCouncil;
    }

    public function getActualQualityNames(): array
    {
        return $this->actualQualityNames;
    }

    public function setActualQualityNames(array $qualityNames): void
    {
        $this->actualQualityNames = $qualityNames;
    }

    public function getFoundTerritorialCouncils(): array
    {
        return $this->foundTerritorialCouncils;
    }

    public function setFoundTerritorialCouncils(array $foundTerritorialCouncils): void
    {
        $this->foundTerritorialCouncils = $foundTerritorialCouncils;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }

    public function setIsResolved(bool $isResolved): void
    {
        $this->isResolved = $isResolved;
    }

    public function __toString(): string
    {
        return \sprintf('%s / %s / %s / %s', $this->type, $this->adherent, $this->qualityName, $this->description);
    }
}
