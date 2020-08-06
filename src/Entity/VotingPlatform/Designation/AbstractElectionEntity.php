<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\EntityDesignationTrait;

abstract class AbstractElectionEntity implements ElectionEntityInterface
{
    use EntityDesignationTrait;

    public function __construct(Designation $designation = null)
    {
        $this->designation = $designation;
    }
}
