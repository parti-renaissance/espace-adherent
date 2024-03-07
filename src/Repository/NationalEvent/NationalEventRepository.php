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
}
