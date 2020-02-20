<?php

namespace AppBundle\Repository;

use AppBundle\Entity\VoteResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class VoteResultRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteResult::class);
    }

    public function getReferentExportQuery(array $referentTags): Query
    {
        $qb = $this
            ->createQueryBuilder('vote_result')
            ->innerJoin('vote_result.votePlace', 'vote_place')
        ;

        $this->applyGeoFilter($qb, $referentTags, 'vote_place', 'country', 'postalCode');

        return $qb->getQuery();
    }

    public function getMunicipalChiefExportQuery(string $postalCode): Query
    {
        return $this
            ->createQueryBuilder('vote_result')
            ->innerJoin('vote_result.votePlace', 'vote_place')
            ->andWhere('FIND_IN_SET(:postal_code, vote_place.postalCode) > 0')
            ->setParameter('postal_code', $postalCode)
            ->getQuery()
        ;
    }
}
