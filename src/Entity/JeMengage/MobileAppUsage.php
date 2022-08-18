<?php

namespace App\Entity\JeMengage;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="jemengage_mobile_app_usage")
 */
class MobileAppUsage
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="date")
     */
    private ?\DateTimeInterface $date = null;

    /**
     * @ORM\Column
     */
    private ?string $zoneType = null;

    /**
     * @ORM\Column
     */
    private ?string $zoneName = null;

    /**
     * @ORM\Column(type="bigint")
     */
    private ?int $uniqueUser = null;
}
