<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class InvitationRepository extends EntityRepository
{
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
