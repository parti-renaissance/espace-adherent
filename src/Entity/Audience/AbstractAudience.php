<?php

declare(strict_types=1);

namespace App\Entity\Audience;

use App\Collection\ZoneCollection;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[MappedSuperclass]
abstract class AbstractAudience implements AudienceInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;
    use AudienceFieldsTrait;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->zones = new ZoneCollection();
    }
}
