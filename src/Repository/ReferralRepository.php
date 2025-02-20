<?php

namespace App\Repository;

use App\Adherent\Referral\StatusEnum;
use App\Entity\Referral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReferralRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referral::class);
    }

    public function isEmailReported(string $email): bool
    {
        return 0 < $this->createQueryBuilder('referral')
            ->select('COUNT(referral.id)')
            ->where('referral.emailAddress = :email')
            ->andWhere('referral.status = :status_reported')
            ->setParameters([
                'email' => $email,
                'status_reported' => StatusEnum::REPORTED,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
