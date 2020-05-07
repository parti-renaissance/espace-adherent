<?php

namespace AppBundle\Repository\VotingPlatform;

use AppBundle\Entity\VotingPlatform\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function findByUuid(string $uuid): ?Election
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
