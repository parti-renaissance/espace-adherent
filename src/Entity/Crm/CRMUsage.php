<?php

namespace App\Entity\Crm;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Crm\CRMUsageRepository")
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(columns={"index"})
 *     }
 * )
 */
class CRMUsage
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $index;

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

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): void
    {
        $this->index = $index;
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
