<?php

declare(strict_types=1);

namespace App\Entity\Audience;

use App\Collection\ZoneCollection;
use App\Entity\EntityZoneTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class AudienceSnapshot extends AbstractAudience
{
    use EntityZoneTrait;

    public function __construct(?Uuid $uuid = null)
    {
        parent::__construct($uuid);

        $this->zones = new ZoneCollection();
    }
}
