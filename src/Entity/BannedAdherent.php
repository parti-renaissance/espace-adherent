<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BannedAdherentRepository")
 */
class BannedAdherent
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
        $this->date = new \DateTime();
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\Datetime $date): void
    {
        $this->date = $date;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self($adherent->getUuid());
    }
}
