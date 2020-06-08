<?php

namespace App\Repository\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DelegatedAccessRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DelegatedAccess::class);
    }

    public function removeFromDelegator(Adherent $adherent): void
    {
        $this->createQueryBuilder('da')
            ->delete()
            ->where('da.delegator = :adherent')
            ->setParameter('adherent', $adherent)
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
}
