<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilMembershipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncilMembership::class);
    }

    /**
     * @return TerritorialCouncilMembership[]
     */
    public function findAvailableMemberships(TerritorialCouncilMembership $membership): array
    {
        return $this
            ->createQueryBuilder('membership')
            ->innerJoin('membership.qualities', 'quality')
            ->innerJoin('membership.adherent', 'adherent')
            ->where('membership.territorialCouncil = :council')
            ->andWhere('quality.name IN (:qualities)')
            ->andWhere('membership.id != :membership_id')
            ->setParameters([
                'council' => $membership->getTerritorialCouncil(),
                'qualities' => $membership->getQualityNames(),
                'membership_id' => $membership->getId(),
            ])
            ->orderBy('adherent.lastName')
            ->addOrderBy('adherent.firstName')
            ->getQuery()
            ->getResult()
        ;
    }
}
