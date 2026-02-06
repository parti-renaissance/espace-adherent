<?php

declare(strict_types=1);

namespace App\Repository\Procuration;

use App\Entity\Adherent;
use App\Entity\Procuration\Request;
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

    public function findDuplicate(?int $id, string $firstNames, string $lastName, \DateTime $birthdate, array $rounds): array
    {
        $queryBuilder = $this->createQueryBuilder('request')
            ->innerJoin('request.requestSlots', 'request_slot')
            ->andWhere('request.firstNames = :first_names')
            ->andWhere('request.lastName = :last_name')
            ->andWhere('request.birthdate = :birthdate')
            ->andWhere('request_slot.round IN (:rounds)')
            ->setParameters([
                'first_names' => $firstNames,
                'last_name' => $lastName,
                'birthdate' => $birthdate,
                'rounds' => $rounds,
            ])
        ;

        if ($id) {
            $queryBuilder
                ->andWhere('request.id != :id')
                ->setParameter('id', $id)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
