<?php

namespace AppBundle\Entity\Election;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ListFieldTrait
{
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

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $candidateFirstName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $candidateLastName;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getCandidateFirstName(): ?string
    {
        return $this->candidateFirstName;
    }

    public function setCandidateFirstName(?string $candidateFirstName): void
    {
        $this->candidateFirstName = $candidateFirstName;
    }

    public function getCandidateLastName(): ?string
    {
        return $this->candidateLastName;
    }

    public function setCandidateLastName(?string $candidateLastName): void
    {
        $this->candidateLastName = $candidateLastName;
    }
}
