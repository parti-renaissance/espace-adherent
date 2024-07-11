<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use App\Repository\ReferentTagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated
 */
#[ORM\Table(name: 'referent_tags')]
#[ORM\Entity(repositoryClass: ReferentTagRepository::class)]
#[UniqueEntity(fields: ['name'])]
#[UniqueEntity(fields: ['code'])]
class ReferentTag
{
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_COUNTRY = 'country';
    public const TYPE_DISTRICT = 'district';
    public const TYPE_BOROUGH = 'borough';
    public const TYPE_METROPOLIS = 'metropolis';

    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: '100')]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'referent_tag.code.invalid')]
    private $code;

    /**
     * Mailchimp Id of the tag
     *
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $externalId;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $type;

    /**
     * @var Zone
     */
    #[ORM\ManyToOne(targetEntity: Zone::class, cascade: ['persist'])]
    private $zone;

    public function __construct(?string $name = null, ?string $code = null, ?Zone $zone = null)
    {
        $this->name = $name;
        $this->code = $code;
        $this->zone = $zone;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isCountryTag(): bool
    {
        return self::TYPE_COUNTRY === $this->type;
    }

    public function isDistrictTag(): bool
    {
        return self::TYPE_DISTRICT === $this->type;
    }

    public function isDepartmentTag(): bool
    {
        return self::TYPE_DEPARTMENT === $this->type;
    }

    public function isBoroughTag(): bool
    {
        return self::TYPE_BOROUGH === $this->type;
    }

    public function isMetropolisTag(): bool
    {
        return self::TYPE_METROPOLIS === $this->type;
    }

    public function getDepartmentCodeFromCirconscriptionName(): ?string
    {
        return $this->isDistrictTag() ? substr($this->code, 6, 2) : null;
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }
}
