<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommitteeElection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeElectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeElection::class);
    }
}
