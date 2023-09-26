<?php

namespace App\Entity\Reporting;

use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="adherent_declared_mandate_history")
 * @ORM\Entity(repositoryClass="App\Repository\Reporting\DeclaredMandateHistoryRepository")
 */
class DeclaredMandateHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Adherent $adherent;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private array $addedMandates = [];

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private array $removedMandates = [];

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeInterface $notifiedAt;

    public function __construct(
        Adherent $adherent,
        array $addedMandates,
        array $removedMandates
    ) {
        $this->adherent = $adherent;
        $this->addedMandates = $addedMandates;
        $this->removedMandates = $removedMandates;
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getAddedMandates(): array
    {
        return $this->addedMandates;
    }

    public function getRemovedMandates(): array
    {
        return $this->removedMandates;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function isNotified(): bool
    {
        return null !== $this->notifiedAt;
    }

    public function setNotified(): void
    {
        $this->notifiedAt = new \DateTimeImmutable();
    }
}
