<?php

declare(strict_types=1);

namespace App\Entity\Audience;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[MappedSuperclass]
abstract class AbstractAudience implements AudienceInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use AudienceFieldsTrait;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}
