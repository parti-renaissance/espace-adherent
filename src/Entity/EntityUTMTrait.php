<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait EntityUTMTrait
{
    #[Groups(['national_event_inscription:webhook', 'hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $utmSource = null;

    #[Groups(['national_event_inscription:webhook', 'hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $utmCampaign = null;
}
