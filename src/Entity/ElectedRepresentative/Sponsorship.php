<?php

declare(strict_types=1);

namespace App\Entity\ElectedRepresentative;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'elected_representative_sponsorship')]
class Sponsorship
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var int|null
     */
    #[Assert\Choice(callback: 'getYears')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'integer')]
    private $presidentialElectionYear;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    private $candidate;

    /**
     * @var ElectedRepresentative|null
     */
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectedRepresentative::class, inversedBy: 'sponsorships')]
    private $electedRepresentative;

    public function __construct(
        ?int $presidentialElectionYear = null,
        ?string $candidate = null,
        ?ElectedRepresentative $electedRepresentative = null,
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
