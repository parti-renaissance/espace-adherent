<?php

namespace App\Repository\AdherentFormation;

use App\Entity\AdherentFormation\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function findAllVisible(): array
    {
        return $this
            ->createQueryBuilder('formation')
            ->andWhere('formation.visible = TRUE')
            ->orderBy('formation.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
