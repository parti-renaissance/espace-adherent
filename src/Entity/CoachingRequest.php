<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class CoachingRequest
{
    /**
     * @var string|null
     */
    #[Assert\Length(max: 1000)]
    #[ORM\Column(nullable: true)]
    private $problemDescription;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 1000)]
    #[ORM\Column(nullable: true)]
    private $proposedSolution;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 1000)]
    #[ORM\Column(nullable: true)]
    private $requiredMeans;

    public function __construct(
        string $problemDescription = '',
        string $proposedSolution = '',
        string $requiredMeans = '',
    ) {
        $this->problemDescription = $problemDescription;
        $this->proposedSolution = $proposedSolution;
        $this->requiredMeans = $requiredMeans;
    }

    public function setProblemDescription(?string $problemDescription): void
    {
        $this->problemDescription = $problemDescription;
    }

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function setProposedSolution(?string $proposedSolution): void
    {
        $this->proposedSolution = $proposedSolution;
    }

    public function getProposedSolution(): ?string
    {
        return $this->proposedSolution;
    }

    public function setRequiredMeans(?string $requiredMeans): void
    {
        $this->requiredMeans = $requiredMeans;
    }

    public function getRequiredMeans(): ?string
    {
        return $this->requiredMeans;
    }
}
