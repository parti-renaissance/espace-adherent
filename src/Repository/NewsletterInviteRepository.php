<?php

namespace App\Repository;

use App\Entity\NewsletterInvite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class NewsletterInviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterInvite::class);
    }

    public function findMostRecentInvite(string $inviteeEmailAddress): ?NewsletterInvite
    {
        return $this
            ->createQueryBuilder('i')
            ->andWhere('LOWER(i.email) = :email')
            ->orderBy('i.createdAt', 'DESC')
            ->setParameter('email', mb_strtolower($inviteeEmailAddress))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
