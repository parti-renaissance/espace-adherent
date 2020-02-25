<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Election;
use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\VotePlace;
use AppBundle\Entity\VoteResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class VoteResultRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteResult::class);
    }

    public function getReferentExportQuery(Election $election, array $referentTags): Query
    {
        $qb = $this->createElectionQueryBuilder($election);

        $this->applyGeoFilter($qb, $referentTags, 'vote_place', 'country', 'postalCode');

        return $qb->getQuery();
    }

    public function getExportQueryByInseeCode(Election $election, string $inseeCode): Query
    {
        return $this
            ->createElectionQueryBuilder($election)
            ->andWhere('vote_place.code LIKE :insee_code')
            ->setParameter('insee_code', $inseeCode.'_%')
            ->getQuery()
        ;
    }

    private function createElectionQueryBuilder(Election $election): QueryBuilder
    {
        return $this
            ->createQueryBuilder('vote_result')
            ->innerJoin('vote_result.votePlace', 'vote_place')
            ->innerJoin('vote_result.electionRound', 'election_round')
            ->innerJoin('election_round.election', 'election')
            ->andWhere('election = :election')
            ->setParameter('election', $election)
        ;
    }

    public function findOneForVotePlace(VotePlace $votePlace, ElectionRound $round): ?VoteResult
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
