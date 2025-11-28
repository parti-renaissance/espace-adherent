<?php

declare(strict_types=1);

namespace App\Entity\JeMengage;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'jemengage_mobile_app_download')]
class MobileAppDownload
{
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?string $zoneType = null;

    #[ORM\Column]
    private ?string $zoneName = null;

    #[ORM\Column(type: 'bigint')]
    private $uniqueUser;

    #[ORM\Column(type: 'integer')]
    private ?int $cumSum = null;

    #[ORM\Column(type: 'float')]
    private ?float $downloadsPer1000 = null;
}
