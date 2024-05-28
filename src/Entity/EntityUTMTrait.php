<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityUTMTrait
{
    /**
     * @ORM\Column(nullable=true)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $utmSource = null;

    /**
     * @ORM\Column(nullable=true)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $utmCampaign = null;
}
