<?php

namespace App\Entity\Crm;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MobileAppUsage
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $zoneType;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $zoneName;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     */
    protected $uniqueUser;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getZoneType(): string
    {
        return $this->zoneType;
    }

    public function setZoneType(string $zoneType): void
    {
        $this->zoneType = $zoneType;
    }

    public function getZoneName(): string
    {
        return $this->zoneName;
    }

    public function setZoneName(string $zoneName): void
    {
        $this->zoneName = $zoneName;
    }

    public function getUniqueUser(): int
    {
        return $this->uniqueUser;
    }

    public function setUniqueUser(int $uniqueUser): void
    {
        $this->uniqueUser = $uniqueUser;
    }
}
