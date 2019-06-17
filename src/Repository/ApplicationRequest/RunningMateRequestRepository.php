<?php

namespace AppBundle\Repository\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RunningMateRequestRepository extends AbstractApplicationRequestRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RunningMateRequest::class);
    }
}
