<?php

namespace App\Repository\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NationalEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NationalEvent::class);
    }

    public function findOneForInscriptions(): ?NationalEvent
    {
        return $this->createQueryBuilder('event')
            ->setMaxResults(1)
            ->orderBy('event.startDate', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneActive(): ?NationalEvent
    {
        return $this->createQueryBuilder('event')
            ->where('event.endDate > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('event.startDate', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneBySlug(string $part): ?NationalEvent
    {
        return $this->findOneBy(['slug' => $part]);
    }

    public function findAllSince(\DateTime $since): array
    {
        return $this->createQueryBuilder('event')
            ->where('event.startDate >= :start_date')
            ->setParameter('start_date', $since)
            ->getQuery()
            ->getResult()
        ;
    }
}
