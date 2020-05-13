<?php

namespace App\Repository;

use App\Entity\NewsletterInvite;
use Doctrine\ORM\EntityRepository;

class NewsletterInviteRepository extends EntityRepository
{
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
