<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Invite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invite::class);
    }

    public function hasEmailAlreadyBeenInvitedRecently(string $email, string $since)
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->where('i.email = :email')
            ->setParameter('email', $email)
            ->andWhere('i.createdAt >= :aDayAgo')
            ->setParameter('aDayAgo', new \DateTime('-'.$since))
            ->getQuery()
            ->getResult()
        ;
    }
}
