<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait GeoPointTrait
{
    /**
     * @var float|null
     */
    #[Groups(['scope'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    private $latitude;

    /**
     * @var float|null
     */
    #[Groups(['scope'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    private $longitude;

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }
}
