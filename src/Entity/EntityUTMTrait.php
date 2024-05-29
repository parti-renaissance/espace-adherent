<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityUTMTrait
{
    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $utmSource = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $utmCampaign = null;
}
