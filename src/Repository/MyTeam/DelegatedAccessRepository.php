<?php

declare(strict_types=1);

namespace App\Repository\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class DelegatedAccessRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DelegatedAccess::class);
    }

    public function findOneByUuid(UuidInterface|string $uuid): ?DelegatedAccess
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function removeFromDelegator(Adherent $adherent, string $type): void
    {
        $this->createQueryBuilder('da')
            ->delete()
            ->where('da.delegator = :adherent AND da.type = :type')
            ->setParameter('adherent', $adherent)
            ->setParameter('type', $type)
            ->getQuery()
            ->execute()
        ;
    }

    public function findDelegatedScopes(Adherent $adherent): array
    {
        return $this->createQueryBuilder('da')
            ->select('DISTINCT da.type')
            ->where('da.delegator = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
