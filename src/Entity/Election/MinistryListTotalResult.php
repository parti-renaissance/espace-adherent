<?php

namespace AppBundle\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MinistryListTotalResult
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
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $total = 0;

    /**
     * @var MinistryVoteResult|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Election\MinistryVoteResult", inversedBy="listTotalResults")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $ministryVoteResult;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getMinistryVoteResult(): ?MinistryVoteResult
    {
        return $this->ministryVoteResult;
    }

    public function setMinistryVoteResult(MinistryVoteResult $ministryVoteResult): void
    {
        $this->ministryVoteResult = $ministryVoteResult;
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
}
