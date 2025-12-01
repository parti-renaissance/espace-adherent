<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentZoneBasedRole;
use App\Entity\Geo\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentZoneBasedRole>
 */
class AdherentZoneBasedRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentZoneBasedRole::class);
    }

    public function findZoneDuplicate(AdherentZoneBasedRole $adherentZoneBasedRole, Zone $zone): ?AdherentZoneBasedRole
    {
        $qb = $this->createQueryBuilder('zone_based_role')
            ->where('zone_based_role.type = :type')
            ->setParameter('type', $adherentZoneBasedRole->getType())
            ->andWhere(':zone MEMBER OF zone_based_role.zones')
            ->setParameter('zone', $zone)
            ->andWhere('zone_based_role.hidden = :false')
            ->setParameter('false', false)
        ;

        if (null !== $adherentZoneBasedRole->getId()) {
            $qb
                ->andWhere('zone_based_role.id != :id')
                ->setParameter('id', $adherentZoneBasedRole->getId())
            ;
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
