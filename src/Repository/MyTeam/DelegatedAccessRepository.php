<?php

namespace App\Repository\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class DelegatedAccessRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DelegatedAccess::class);
    }

    public function findOneByUuid(string $uuid): ?DelegatedAccess
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

    public function findAllDelegatedAccessForUser(Adherent $adherent)
    {
        return $this->createQueryBuilder('da')
            ->where('da.delegated = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDelegatedAccessFor(Adherent $adherent, string $type)
    {
        try {
            return $this->createQueryBuilder('da')
                ->where('da.delegated = :adherent')
                ->andWhere('da.type = :type')
                ->setParameter('adherent', $adherent)
                ->setParameter('type', $type)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $exception) {
            throw new \LogicException("User have multiple \"$type\" delegated accesses.");
        }
    }

    public function hasDelegatedAccessWithScopeFeatures(Adherent $adherent, array $types): array
    {
        return $this->createQueryBuilder('delegatedAccess')
            ->where('delegatedAccess.delegated = :adherent')
            ->andWhere('delegatedAccess.scopeFeatures IS NOT NULL')
            ->andWhere('delegatedAccess.type IN (:types)')
            ->setParameters([
                'adherent' => $adherent,
                'types' => $types,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
