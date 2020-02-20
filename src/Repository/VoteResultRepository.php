<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Election;
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

    public function getMunicipalChiefExportQuery(Election $election, string $postalCode): Query
    {
        return $this
            ->createElectionQueryBuilder($election)
            ->andWhere('FIND_IN_SET(:postal_code, vote_place.postalCode) > 0')
            ->setParameter('postal_code', $postalCode)
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
}
