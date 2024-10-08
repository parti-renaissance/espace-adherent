<?php

namespace App\Repository;

use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\Geo\Zone;
use App\Entity\PushToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;

class PushTokenRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushToken::class);
    }

    public function findIdentifiersForAdherent(Adherent $adherent): array
    {
        $tokens = $this->createQueryBuilder('token')
            ->select('DISTINCT(token.identifier)')
            ->where('token.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map('current', $tokens);
    }

    public function findIdentifiersForZones(array $zones): array
    {
        $qb = $this->createQueryBuilder('token')
            ->select('DISTINCT(token.identifier)')
            ->leftJoin('token.adherent', 'adherent')
            ->leftJoin('token.device', 'device')
        ;

        $adherentZonesCondition = $this->createGeoZonesQueryBuilder(
            $zones,
            $qb,
            Adherent::class,
            'adherent_2',
            'zones',
            'adherent_zone_2',
            null,
            true,
            'adherent_zone_parent'
        );

        $deviceZoneCondition = $this->createGeoZonesQueryBuilder(
            $zones,
            $qb,
            Device::class,
            'device_2',
            'zones',
            'device_zone_2',
            null,
            true,
            'device_zone_parent'
        );

        $tokens = $qb
            ->andWhere((new Orx())
                ->add(\sprintf('adherent.id IN (%s)', $adherentZonesCondition->getDQL()))
                ->add(\sprintf('device.id IN (%s)', $deviceZoneCondition->getDQL()))
            )
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map('current', $tokens);
    }

    public function findByIdentifier(string $identifier): ?PushToken
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    public function findAllForZone(Zone $zone): array
    {
        $queryBuilder = $this
            ->createQueryBuilder('t')
            ->select('t.identifier')
            ->innerJoin('t.adherent', 'a')
        ;

        $this->withGeoZones(
            [$zone],
            $queryBuilder,
            'a',
            Adherent::class,
            'a2',
            'zones',
            'z'
        );

        return $queryBuilder->getQuery()->getSingleColumnResult();
    }

    public function findAllForActionInscriptions(Action $action): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.identifier')
            ->innerJoin('t.adherent', 'a')
            ->innerJoin(ActionParticipant::class, 'ap', Join::WITH, 'ap.adherent = a')
            ->where('ap.action = :action')
            ->setParameter('action', $action)
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
