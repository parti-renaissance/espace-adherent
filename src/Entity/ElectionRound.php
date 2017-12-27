<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_rounds")
 *
 * @UniqueEntity({"election", "label"})
 */
class ElectionRound
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $label = '';

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $description;

    /**
     * @var Election|null
     *
     * @ORM\ManyToOne(targetEntity="Election", inversedBy="rounds")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull
     */
    private $election;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull
     * @Assert\GreaterThan("now")
     */
    private $date;

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
