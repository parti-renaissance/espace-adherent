<?php

namespace App\Repository\Procuration;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProxyStatusEnum;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class ProxyRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proxy::class);
    }

    public function countAvailableProxies(Request $request): int
    {
        if (!$request->votePlace && !$request->customVotePlace) {
            return 0;
        }

        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id)')
            ->where('p.status = :status')
            ->setParameter('status', ProxyStatusEnum::PENDING)
        ;

        if ($request->votePlace) {
            $qb
                ->andWhere('p.votePlace = :votePlace')
                ->setParameter('votePlace', $request->votePlace)
            ;
        } else {
            $qb
                ->andWhere('p.customVotePlace = :customVotePlace')
                ->setParameter('customVotePlace', $request->customVotePlace)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findAvailableProxies(Request $request, int $page): PaginatorInterface
    {
        $queryBuilder = $this->createQueryBuilder('proxy');

        $orx = $queryBuilder->expr()->orX();

        if ($votePlace = $request->votePlace) {
            $city = $votePlace->getParentsOfType(Zone::CITY);
            $department = $votePlace->getParentsOfType(Zone::DEPARTMENT);

            $orx->add('proxy.votePlace = :vote_place');
            $orx->add('zone_parent IN (:parent_zones)');

            $caseSelect = 'CASE WHEN vote_place = :vote_place THEN 2
                    WHEN zone_parent = :city THEN 1
                    ELSE 0 END AS score';

            $queryBuilder
                ->innerJoin('proxy.votePlace', 'vote_place')
                ->leftJoin('vote_place.parents', 'zone_parent', Join::WITH, 'zone_parent.type IN (:parent_types)')
                ->setParameter('parent_types', [Zone::CITY, Zone::DEPARTMENT])
                ->setParameter('parent_zones', [$city, $department])
                ->setParameter('vote_place', $votePlace)
                ->setParameter('city', $city)
            ;
        } else {
            $voteZone = $request->voteZone;
            $caseSelect = 'CASE WHEN vote_zone = :vote_zone THEN 1 ELSE 0 END AS score';

            $orx->add('vote_zone = :vote_zone');
            $orx->add('zone_parent IN (:parent_zones)');

            if ($votePlace = $request->customVotePlace) {
                $caseSelect = 'CASE WHEN proxy.customVotePlace = :vote_place THEN 2
                    WHEN vote_zone = :vote_zone THEN 1
                    ELSE 0 END AS score';
                $orx->add('proxy.customVotePlace = :vote_place');
                $queryBuilder->setParameter('vote_place', $votePlace);
            }

            $queryBuilder
                ->innerJoin('proxy.voteZone', 'vote_zone')
                ->leftJoin('vote_zone.parents', 'zone_parent', Join::WITH, 'zone_parent.type IN (:parent_types)')
                ->setParameter('parent_types', [Zone::CITY, Zone::DEPARTMENT, Zone::COUNTRY])
                ->setParameter('parent_zones', $voteZone->getWithParents([Zone::CITY, Zone::DEPARTMENT, Zone::COUNTRY]))
                ->setParameter('vote_zone', $voteZone)
            ;
        }

        $queryBuilder
            ->addSelect($caseSelect)
            ->andWhere('proxy.status = :status')
            ->andWhere($orx)
            ->orderBy('score', 'desc')
            ->setParameter('status', ProxyStatusEnum::PENDING)
        ;

        return $this->configurePaginator($queryBuilder, $page);
    }
}
