<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\BannedAdherent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BannedAdherentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannedAdherent::class);
    }

    public function countForEmail(string $email): int
    {
        return $this->count(['uuid' => Adherent::createUuid($email)]);
    }
}
