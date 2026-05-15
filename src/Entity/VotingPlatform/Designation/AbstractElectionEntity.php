<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\EntityDesignationTrait;
use App\Entity\EntityIdentityTrait;
use Symfony\Component\Uid\Uuid;

abstract class AbstractElectionEntity implements ElectionEntityInterface
{
    use EntityDesignationTrait;
    use EntityIdentityTrait;

    public function __construct(?Designation $designation = null, ?Uuid $uuid = null)
    {
        $this->designation = $designation;
        $this->uuid = $uuid ?? Uuid::v4();
    }
}
