<?php

namespace AppBundle\Repository\Election;

use AppBundle\Entity\Election;
use AppBundle\Entity\Election\VotePlaceResult;
use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\VotePlace;
use AppBundle\Repository\GeoFilterTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class VotePlaceResultRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VotePlaceResult::class);
    }

    public function getReferentExportQuery(Election $election, array $referentTags): Query
    {
        $qb = $this->createElectionQueryBuilder($election, 'vote_result');

        $this->applyGeoFilter($qb, $referentTags, 'vote_place', 'country', 'postalCode');

        return $qb->getQuery();
    }

    public function getExportQueryByInseeCodes(Election $election, array $inseeCodes): Query
    {
        return $this
            ->createElectionQueryBuilder($election, $alias = 'vote_result')
            ->andWhere('SUBSTRING_INDEX(vote_place.code, \'_\', 1) IN (:insee_codes)')
            ->setParameter('insee_codes', $inseeCodes)
            ->getQuery()
        ;
    }

    private function createElectionQueryBuilder(Election $election, string $alias = 'vote_result'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->innerJoin($alias.'.votePlace', 'vote_place')
            ->innerJoin($alias.'.electionRound', 'election_round')
            ->innerJoin('election_round.election', 'election')
            ->andWhere('election = :election')
            ->setParameter('election', $election)
        ;
    }

    public function findOneForVotePlace(VotePlace $votePlace, ElectionRound $round): ?VotePlaceResult
    {
        return $this->createQueryBuilder('vr')
            ->where('vr.votePlace = :vote_place')
            ->andWhere('vr.electionRound = :round')
            ->setParameters([
                'vote_place' => $votePlace,
                'round' => $round,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
