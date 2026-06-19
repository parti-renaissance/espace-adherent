<?php

declare(strict_types=1);

namespace App\Repository\Pronostic;

use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticReminder;
use App\Pronostic\PronosticReminderTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PronosticReminderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PronosticReminder::class);
    }

    public function has(Pronostic $pronostic, PronosticReminderTypeEnum $type): bool
    {
        return (bool) $this->count(['pronostic' => $pronostic, 'type' => $type]);
    }
}
