<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FailedLoginAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FailedLoginAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FailedLoginAttempt::class);
    }

    public function save(FailedLoginAttempt $failedLoginAttempt): void
    {
        $this->_em->persist($failedLoginAttempt);
        $this->_em->flush();
    }
}
