<?php

namespace App\Repository\Instance\NationalCouncil;

use App\Entity\Instance\NationalCouncil\Candidacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidacyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidacy::class);
    }
}
