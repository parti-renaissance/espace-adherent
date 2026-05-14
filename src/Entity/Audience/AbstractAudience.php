<?php

declare(strict_types=1);

namespace App\Entity\Audience;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;

#[MappedSuperclass]
abstract class AbstractAudience implements AudienceInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use AudienceFieldsTrait;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
    }
}
