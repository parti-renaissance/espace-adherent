<?php

namespace App\Repository\Coalition;

use App\Entity\Coalition\Coalition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CoalitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coalition::class);
    }

    public function findFollowedBy(UserInterface $user): array
    {
        return $this->createQueryBuilder('coalition')
            ->leftJoin('coalition.followers', 'follower')
            ->andWhere('follower.adherent = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}
