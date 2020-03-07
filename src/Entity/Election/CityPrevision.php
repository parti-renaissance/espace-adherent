<?php

namespace AppBundle\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_city_prevision")
 *
 * @Algolia\Index(autoIndex=false)
 */
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
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Choice(choices=CityPrevision::STRATEGY_CHOICES)
     */
    private $strategy;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $alliances;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $allies;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $validatedBy;

    public function __construct(
        ?string $strategy = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $alliances = null,
        ?string $allies = null,
        ?string $validatedBy = null
    ) {
        $this->strategy = $strategy;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
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
            && !$this->firstName
            && !$this->lastName
            && !$this->alliances
            && !$this->allies
        ;
    }
}
