<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VolunteerRequestRepository extends ApplicationRequestRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VolunteerRequest::class);
    }
}
