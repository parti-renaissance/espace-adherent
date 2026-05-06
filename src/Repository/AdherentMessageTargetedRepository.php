<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageTargeted;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentMessageTargetedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentMessageTargeted::class);
    }

    public function deleteForMessagesSentBefore(\DateTimeInterface $threshold): int
    {
        return (int) $this->createQueryBuilder('t')
            ->delete()
            ->where('t.message IN (
                SELECT m.id FROM '.AdherentMessage::class.' m
                WHERE m.sentAt IS NOT NULL AND m.sentAt <= :threshold
            )')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute();
    }
}
