<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PoliticalCommitteeMembershipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PoliticalCommitteeMembership::class);
    }

    public function countLeaderAndMayorMembersFor(PoliticalCommittee $politicalCommittee): int
    {
        return (int) $this->createQueryBuilder('membership')
            ->select('COUNT(1)')
            ->innerJoin('membership.qualities', 'quality')
            ->where('membership.politicalCommittee = :politicalCommittee')
            ->andWhere('quality.name in (:qualities)')
            ->setParameter('politicalCommittee', $politicalCommittee)
            ->setParameter('qualities', [TerritorialCouncilQualityEnum::MAYOR, TerritorialCouncilQualityEnum::LEADER])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
