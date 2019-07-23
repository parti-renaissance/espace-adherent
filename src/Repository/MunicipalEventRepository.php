<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\MunicipalEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MunicipalEventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MunicipalEvent::class);
    }

    public function findEventsByOrganizer(Adherent $organizer): array
    {
        return $this
            ->createQueryBuilder('e')
            ->andWhere('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countEventForOrganizer(Adherent $organizer): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(1)')
            ->where('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
