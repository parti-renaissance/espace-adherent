<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\TerritorialCouncil\Filter\MembersListFilter;
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

    public function countForPoliticalCommittee(PoliticalCommittee $politicalCommittee, array $qualities = []): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(1)')
            ->where('m.politicalCommittee = :political_committee')
            ->setParameter('political_committee', $politicalCommittee)
        ;

        if ($qualities) {
            $qb
                ->innerJoin('m.qualities', 'quality')
                ->andWhere('quality.name IN (:qualities)')
                ->setParameter('qualities', $qualities)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return PoliticalCommitteeMembership[]
     */
    public function searchByFilter(MembersListFilter $filter): array
    {
        $qb = $this
            ->createQueryBuilder('m')
            ->addSelect('political_committee')
            ->innerJoin('m.adherent', 'adherent')
            ->innerJoin('m.politicalCommittee', 'political_committee')
            ->leftJoin('m.qualities', 'quality')
        ;

        if ($filter->getPoliticalCommittee()) {
            $qb
                ->andWhere('political_committee = :political_committee')
                ->setParameter('political_committee', $filter->getPoliticalCommittee())
            ;
        }

        if ($qualities = $filter->getQualities()) {
            $qb
                ->andWhere('quality.name in (:qualities)')
                ->setParameter('qualities', $qualities)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
