<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RunningMateRequestRepository extends ApplicationRequestRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RunningMateRequest::class);
    }
}
