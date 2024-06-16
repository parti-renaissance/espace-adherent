<?php

namespace App\Repository\Procuration;

use App\Entity\Adherent;
use App\Entity\ProcurationV2\Request;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RequestRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    public function hasUpcomingRequest(Adherent $adherent): bool
    {
        $result = $this->createQueryBuilder('request')
            ->select('COUNT(DISTINCT request)')
            ->andWhere('request.adherent = :adherent')
            ->innerJoin('request.requestSlots', 'request_slot')
            ->innerJoin('request_slot.round', 'round')
            ->andWhere('round.date > NOW()')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $result > 0;
    }
}
