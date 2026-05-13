<?php

declare(strict_types=1);

namespace App\Repository\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\PublicationStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PublicationStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationStatistics::class);
    }

    public function findOneByMessage(AdherentMessage $message): ?PublicationStatistics
    {
        return $this->findOneBy(['message' => $message]);
    }
}
