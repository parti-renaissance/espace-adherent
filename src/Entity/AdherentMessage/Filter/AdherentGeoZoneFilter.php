<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class AdherentGeoZoneFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;

    /**
     * @var Zone
     */
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    public function __construct(?Zone $zone = null)
    {
        parent::__construct();

        $this->zone = $zone;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }
}
