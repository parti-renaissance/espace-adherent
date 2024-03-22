<?php

namespace App\Repository\Procuration;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProxyStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProxyRepository extends ServiceEntityRepository
{
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
}
