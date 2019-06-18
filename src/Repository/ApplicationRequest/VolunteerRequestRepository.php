<?php

namespace AppBundle\Repository\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VolunteerRequestRepository extends AbstractApplicationRequestRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VolunteerRequest::class);
    }
}
