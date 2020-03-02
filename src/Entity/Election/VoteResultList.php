<?php

namespace AppBundle\Entity\Election;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class VoteResultList
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var VoteResultListCollection
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Election\VoteResultListCollection", inversedBy="lists")
     */
    private $listCollection;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $nuance;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $adherentCount;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eligibleCount;

    public function __construct(
        string $label = null,
        string $nuance = null,
        int $adherentCount = null,
        int $eligibleCount = null
    ) {
        $this->label = $label;
        $this->nuance = $nuance;
        $this->adherentCount = $adherentCount;
        $this->eligibleCount = $eligibleCount;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getNuance(): ?string
    {
        return $this->nuance;
    }

    public function setNuance(?string $nuance): void
    {
        $this->nuance = $nuance;
    }

    public function getAdherentCount(): ?int
    {
        return $this->adherentCount;
    }

    public function setAdherentCount(?int $adherentCount): void
    {
        $this->adherentCount = $adherentCount;
    }

    public function getEligibleCount(): ?int
    {
        return $this->eligibleCount;
    }

    public function setEligibleCount(?int $eligibleCount): void
    {
        $this->eligibleCount = $eligibleCount;
    }

    public function getListCollection(): ?VoteResultListCollection
    {
        return $this->listCollection;
    }

    public function setListCollection(?VoteResultListCollection $listCollection): void
    {
        $this->listCollection = $listCollection;
    }
}
