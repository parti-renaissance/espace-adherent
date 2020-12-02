<?php

namespace App\Repository\ApplicationRequest;

use App\Entity\ApplicationRequest\RunningMateRequest;
use Doctrine\Common\Persistence\ManagerRegistry;

class RunningMateRequestRepository extends AbstractApplicationRequestRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RunningMateRequest::class);
    }
}
