<?php

namespace App\Entity\Election;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'election_city_prevision')]
#[ORM\Entity]
class CityPrevision
{
    public const STRATEGY_FUSION = 'fusion';
    public const STRATEGY_RETENTION = 'retention';

    public const STRATEGY_CHOICES = [
        self::STRATEGY_FUSION,
        self::STRATEGY_RETENTION,
    ];

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var string|null
     *
     * @Assert\Choice(choices=CityPrevision::STRATEGY_CHOICES)
     */
    #[ORM\Column(nullable: true)]
    private $strategy;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    #[ORM\Column(nullable: true)]
    private $name;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    #[ORM\Column(nullable: true)]
    private $alliances;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    #[ORM\Column(nullable: true)]
    private $allies;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    #[ORM\Column(nullable: true)]
    private $validatedBy;

    public function __construct(
        ?string $strategy = null,
        ?string $name = null,
        ?string $alliances = null,
        ?string $allies = null,
        ?string $validatedBy = null
    ) {
        $this->strategy = $strategy;
        $this->name = $name;
        $this->alliances = $alliances;
        $this->allies = $allies;
        $this->validatedBy = $validatedBy;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    public function setStrategy(?string $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAlliances(): ?string
    {
        return $this->alliances;
    }

    public function setAlliances(?string $alliances): void
    {
        $this->alliances = $alliances;
    }

    public function getAllies(): ?string
    {
        return $this->allies;
    }

    public function setAllies(?string $allies): void
    {
        $this->allies = $allies;
    }

    public function getValidatedBy(): ?string
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?string $validatedBy): void
    {
        $this->validatedBy = $validatedBy;
    }

    public function isEmpty(): bool
    {
        return !$this->strategy
            && !$this->name
            && !$this->alliances
            && !$this->allies;
    }
}
