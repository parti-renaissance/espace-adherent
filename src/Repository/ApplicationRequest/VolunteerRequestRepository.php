<?php

namespace App\Repository\ApplicationRequest;

use App\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\Persistence\ManagerRegistry;

class VolunteerRequestRepository extends AbstractApplicationRequestRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VolunteerRequest::class);
    }
}
