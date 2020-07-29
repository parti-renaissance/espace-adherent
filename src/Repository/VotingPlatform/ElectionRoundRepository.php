<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\ElectionRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ElectionRoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectionRound::class);
    }

    public function findByUuid(string $uuid): ?ElectionRound
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
