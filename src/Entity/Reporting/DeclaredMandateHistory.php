<?php

declare(strict_types=1);

namespace App\Entity\Reporting;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Repository\Reporting\DeclaredMandateHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeclaredMandateHistoryRepository::class)]
#[ORM\Table(name: 'adherent_declared_mandate_history')]
class DeclaredMandateHistory
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private Adherent $adherent;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $addedMandates;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $removedMandates;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $notifiedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $telegramNotifiedAt = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private ?Administrator $administrator = null;

    public function __construct(
        Adherent $adherent,
        array $addedMandates,
        array $removedMandates,
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

    public function setNotifiedAt(\DateTimeInterface $notifiedAt): void
    {
        $this->notifiedAt = $notifiedAt;
    }

    public function isTelegramNotifiedAt(): bool
    {
        return null !== $this->telegramNotifiedAt;
    }

    public function setTelegramNotifiedAt(\DateTimeInterface $telegramNotifiedAt): void
    {
        $this->telegramNotifiedAt = $telegramNotifiedAt;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }
}
