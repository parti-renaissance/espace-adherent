<?php

namespace App\Repository\Procuration;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\RequestSlot;
use App\Entity\ProcurationV2\Round;
use App\Procuration\V2\ProxyStatusEnum;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class ProxyRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use UuidEntityRepositoryTrait;
    use GeoZoneTrait;

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

    public function findAvailableProxies(Request $request, Round $round, int $page): PaginatorInterface
    {
        $queryBuilder = $this->createQueryBuilder('proxy');
        $orx = $queryBuilder->expr()->orX();

        $firstLevel = $request->votePlace ?? $request->voteZone;
        $caseSelect = 'CASE WHEN FIND_IN_SET(:first_level_id, proxy.zoneIds) > 0 THEN '.(match ($firstLevel->getType()) {
            Zone::VOTE_PLACE => 8,
            Zone::BOROUGH => 4,
            Zone::CITY => 2,
            default => 0,
        });
        $orx->add('FIND_IN_SET(:first_level_id, proxy.zoneIds) > 0');
        $queryBuilder->setParameter('first_level_id', $firstLevel->getId());

        if ($secondLevel = current($firstLevel->getParentsOfType($firstLevel->isParis() ? Zone::BOROUGH : Zone::CITY))) {
            $caseSelect .= ' WHEN FIND_IN_SET(:second_level_id, proxy.zoneIds) > 0 THEN '.($secondLevel->isBorough() ? 4 : 2);
            $orx->add('FIND_IN_SET(:second_level_id, proxy.zoneIds) > 0');
            $queryBuilder->setParameter('second_level_id', $secondLevel->getId());
        }

        if ($thirdLevel = current($firstLevel->getParentsOfType($firstLevel->isInFrance() ? Zone::DEPARTMENT : Zone::COUNTRY))) {
            $caseSelect .= ' ELSE '.($thirdLevel->isDepartment() ? '1' : '0').' END AS score';
            $orx->add('FIND_IN_SET(:third_level_id, proxy.zoneIds) > 0');
            $queryBuilder->setParameter('third_level_id', $thirdLevel->getId());
        } else {
            $caseSelect .= ' ELSE 0 END AS score';
        }

        $queryBuilder
            ->addSelect($caseSelect)
            ->innerJoin('proxy.proxySlots', 'proxy_slot')
            ->leftJoin(RequestSlot::class, 'request_slot', Join::WITH, 'request_slot.proxySlot = proxy_slot')
            ->andWhere('proxy.status = :status')
            ->andWhere($orx)
            ->andWhere('proxy_slot.round = :round')
            ->andWhere('request_slot IS NULL')
            ->orderBy('score', 'desc')
            ->setParameter('status', ProxyStatusEnum::PENDING)
            ->setParameter('round', $round)
        ;

        return $this->configurePaginator($queryBuilder, $page);
    }

    public function hasUpcomingProxy(Adherent $adherent): bool
    {
        $result = $this->createQueryBuilder('proxy')
            ->select('COUNT(DISTINCT proxy)')
            ->andWhere('proxy.adherent = :adherent')
            ->innerJoin('proxy.proxySlots', 'proxy_slot')
            ->innerJoin('proxy_slot.round', 'round')
            ->andWhere('round.date > NOW()')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $result > 0;
    }

    public function findDuplicate(string $firstNames, string $lastName, \DateTime $birthdate, array $rounds): array
    {
        return $this->createQueryBuilder('proxy')
            ->innerJoin('proxy.proxySlots', 'proxy_slot')
            ->andWhere('proxy.firstNames = :first_names')
            ->andWhere('proxy.lastName = :last_name')
            ->andWhere('proxy.birthdate = :birthdate')
            ->andWhere('proxy_slot.round IN (:rounds)')
            ->setParameters([
                'first_names' => $firstNames,
                'last_name' => $lastName,
                'birthdate' => $birthdate,
                'rounds' => $rounds,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
