<?php

namespace App\Repository\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\MyTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MyTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyTeam::class);
    }

    public function findOneByAdherentAndScope(Adherent $adherent, string $scope): ?MyTeam
    {
        return $this->createQueryBuilder('my_team')
            ->where('my_team.owner = :adherent AND my_team.scope = :scope')
            ->setParameters([
                'adherent' => $adherent,
                'scope' => $scope,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
