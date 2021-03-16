<?php

namespace App\Repository\Coalition;

use App\Entity\Coalition\Cause;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CauseRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cause::class);
    }

    public function findFollowedByUuids(array $uuids, UserInterface $user): array
    {
        self::validUuids($uuids);

        return $this->createQueryBuilder('cause')
            ->innerJoin('cause.followers', 'follower')
            ->andWhere('follower.adherent = :adherent')
            ->andWhere('cause.uuid IN (:uuids)')
            ->setParameter('adherent', $user)
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;
    }
}
