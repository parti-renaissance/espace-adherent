<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommitteeCandidacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeCandidacyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeCandidacy::class);
    }
}
