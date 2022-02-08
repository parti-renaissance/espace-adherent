<?php

namespace App\Repository\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\Member;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MemberRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function isMemberWithScopeFeatures(Adherent $adherent, array $scopes): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.team', 'team')
            ->where('m.adherent = :adherent')
            ->andWhere('m.scopeFeatures IS NOT NULL')
            ->andWhere('team.scope IN (:scopes)')
            ->setParameters([
                'adherent' => $adherent,
                'scopes' => $scopes,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
