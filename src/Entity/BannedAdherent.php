<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BannedAdherentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: BannedAdherentRepository::class)]
class BannedAdherent
{
    use EntityIdentityTrait;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
        $this->date = new \DateTimeImmutable();
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self(Adherent::createUuid($adherent->getEmailAddress()));
    }
}
