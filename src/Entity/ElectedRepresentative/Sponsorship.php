<?php

namespace AppBundle\Entity\ElectedRepresentative;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="elected_representative_sponsorship")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Sponsorship
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback="getYears")
     */
    private $presidentialElectionYear;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\Length(max="255")
     */
    private $candidate;

    /**
     * @var ElectedRepresentative|null
     *
     * @ORM\ManyToOne(targetEntity="ElectedRepresentative", inversedBy="sponsorships")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $electedRepresentative;

    public function __construct(
        int $presidentialElectionYear = null,
        string $candidate = null,
        ElectedRepresentative $electedRepresentative = null
    ) {
        $this->electedRepresentative = $electedRepresentative;
        $this->candidate = $candidate;
        $this->presidentialElectionYear = $presidentialElectionYear;
    }

    public static function getYears(): array
    {
        return range(2007, 2022, 5);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): ?string
    {
        return $this->candidate;
    }

    public function setCandidate(?string $candidate = null): void
    {
        $this->candidate = $candidate;
    }

    public function getPresidentialElectionYear(): ?int
    {
        return $this->presidentialElectionYear;
    }

    public function setPresidentialElectionYear(int $presidentialElectionYear): void
    {
        $this->presidentialElectionYear = $presidentialElectionYear;
    }

    public function getElectedRepresentative(): ?ElectedRepresentative
    {
        return $this->electedRepresentative;
    }

    public function setElectedRepresentative(ElectedRepresentative $electedRepresentative): void
    {
        $this->electedRepresentative = $electedRepresentative;
    }
}
