<?php

declare(strict_types=1);

namespace App\Repository\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\Member;
use App\Entity\MyTeam\MyTeam;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\MyTeam\Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findMemberInTeam(MyTeam $team, Adherent $member): ?Member
    {
        return $this->createQueryBuilder('m')
            ->where('m.team = :team')
            ->andWhere('m.adherent = :adherent')
            ->setParameter('adherent', $member)
            ->setParameter('team', $team)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
