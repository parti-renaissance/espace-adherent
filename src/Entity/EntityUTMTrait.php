<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityUTMTrait
{
    /** @ORM\Column(nullable=true) */
    public ?string $utmSource = null;

    /** @ORM\Column(nullable=true) */
    public ?string $utmCampaign = null;
}
