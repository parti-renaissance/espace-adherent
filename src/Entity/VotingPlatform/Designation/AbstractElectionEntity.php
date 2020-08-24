<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\EntityDesignationTrait;
use App\Entity\EntityIdentityTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractElectionEntity implements ElectionEntityInterface
{
    use EntityDesignationTrait;
    use EntityIdentityTrait;

    public function __construct(Designation $designation = null, UuidInterface $uuid = null)
    {
        $this->designation = $designation;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}
