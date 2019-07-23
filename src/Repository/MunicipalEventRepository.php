<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\MunicipalEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MunicipalEventRepository extends EventRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MunicipalEvent::class);
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
