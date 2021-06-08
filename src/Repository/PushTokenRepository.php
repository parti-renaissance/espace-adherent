<?php

namespace App\Repository;

use App\Entity\PushToken;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;

class PushTokenRepository extends EventRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushToken::class);
    }

    public function findIdentifiersForZones(array $zones): array
    {
        return $this->createQueryBuilder('token')
            ->select('DISTINCT(token.identifier')
            ->leftJoin('token.adherent', 'adherent')
            ->leftJoin('token.device', 'device')
            ->leftJoin('device.zones', 'device_zone')
            ->leftJoin('adherent.zones', 'adherent_zone')
            ->andWhere((new Orx())
                ->add('adherent_zone IN :zones OR adherent_zone.parents IN :zones OR adherent_zone.children IN :zones')
                ->add('device_zone IN :zones OR device_zone.parents IN :zones OR device_zone.children IN :zones')
            )
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
