<?php

namespace App\Repository\Procuration;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\RequestSlot;
use App\Entity\ProcurationV2\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RequestSlotRepository extends ServiceEntityRepository
{
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
}
