<?php

declare(strict_types=1);

namespace App\Repository\Procuration;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\RequestSlot;
use App\Entity\ProcurationV2\Round;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class RequestSlotRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestSlot::class);
    }

    public function matchingAlreadyExists(Request $request, Proxy $proxy, Round $round): bool
    {
        $count = (int) $this->createQueryBuilder('request_slot')
            ->select('COUNT(DISTINCT request_slot)')
            ->andWhere('request_slot.request = :request')
            ->innerJoin('request_slot.proxySlot', 'proxy_slot')
            ->andWhere('proxy_slot.proxy = :proxy')
            ->andWhere('request_slot.round = :round')
            ->setParameters([
                'request' => $request,
                'proxy' => $proxy,
                'round' => $round,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $count > 0;
    }

    public function findAllMatchedToRemindQueryBuilder(Round $round, ?\DateTime $matchedBefore = null): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('request_slot')
            ->select('PARTIAL request_slot.{id, uuid}')
            ->where('request_slot.round = :round')
            ->andWhere('request_slot.proxySlot IS NOT NULL')
            ->andWhere('request_slot.matchRemindedAt IS NULL')
            ->setParameter('round', $round)
        ;

        if ($matchedBefore) {
            $qb
                ->andWhere('request_slot.updatedAt <= :matched_before')
                ->setParameter('matched_before', $matchedBefore)
            ;
        }

        return $qb;
    }
}
