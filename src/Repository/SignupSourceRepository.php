<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SignupSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SignupSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SignupSource::class);
    }

    public function findOneByCode(string $code): ?SignupSource
    {
        return $this->findOneBy(['code' => $code]);
    }
}
