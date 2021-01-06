<?php

namespace App\Repository\TerritorialCouncil;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\TerritorialCouncil\OfficialReport;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OfficialReportRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OfficialReport::class);
    }

    /**
     * @return OfficialReport[]|PaginatorInterface
     */
    public function getPaginator(array $referentTags, int $page = 1, int $limit = 30): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('report')
            ->innerJoin('report.politicalCommittee', 'politicalCommittee')
            ->innerJoin('politicalCommittee.territorialCouncil', 'territorialCouncil')
            ->innerJoin('territorialCouncil.referentTags', 'tag')
            ->where('tag IN (:tags)')
            ->setParameter('tags', $referentTags)
            ->orderBy('report.createdAt', 'DESC')
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }

    /**
     * @return OfficialReport[]
     */
    public function getReportsForPoliticalCommittee(PoliticalCommittee $politicalCommittee): array
    {
        return $this->createQueryBuilder('report')
            ->where('report.politicalCommittee = :pc')
            ->setParameter('pc', $politicalCommittee)
            ->orderBy('report.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
