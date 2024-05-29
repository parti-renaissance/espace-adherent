<?php

namespace App\Entity;

use App\Repository\ElectionRoundRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity({"election", "label"})
 */
#[ORM\Table(name: 'election_rounds')]
#[ORM\Entity(repositoryClass: ElectionRoundRepository::class)]
class ElectionRound
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[ORM\Column]
    private $label = '';

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    #[ORM\Column(nullable: true)]
    private $description;

    /**
     * @var Election|null
     *
     * @Assert\NotNull
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'rounds')]
    private $election;

    /**
     * @var \DateTimeInterface|null
     *
     * @Assert\NotNull
     * @Assert\GreaterThan("now")
     */
    #[ORM\Column(type: 'date')]
    private $date;

    public function __toString(): string
    {
        return $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getElection(): ?Election
    {
        return $this->election;
    }

    public function setElection(?Election $election): void
    {
        $this->election = $election;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        if ($this->date instanceof \DateTime) {
            $this->date = \DateTimeImmutable::createFromMutable($this->date);
        }

        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function isPassed(): bool
    {
        return new \DateTime() >= $this->date;
    }
}
