<?php

declare(strict_types=1);

namespace App\Repository\NationalEvent;

use App\Entity\NationalEvent\InscriptionReminder;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InscriptionReminderRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionReminder::class);
    }
}
