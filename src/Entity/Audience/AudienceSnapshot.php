<?php

declare(strict_types=1);

namespace App\Entity\Audience;

use App\Entity\EntityZoneTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AudienceSnapshot extends AbstractAudience
{
    use EntityZoneTrait;
}
