<?php

declare(strict_types=1);

namespace App\Repository\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\PublicationStatistics;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PublicationStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationStatistics::class);
    }

    public function findOneByMessage(AdherentMessage $message): ?PublicationStatistics
    {
        return $this->findOneBy(['message' => $message]);
    }

    public function findNationalReportRatio(string $instanceScope, int $maxHistory = 30): array
    {
        return $this->createReportRatioQueryBuilder($instanceScope, $maxHistory)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function findLocalReportRatio(string $instanceScope, array $zones, int $maxHistory = 30): array
    {
        return $this->createReportRatioQueryBuilder($instanceScope, $maxHistory)
            ->innerJoin(AdherentMessageFilter::class, 'filter', Join::WITH, 'message.filter = filter')
            ->innerJoin('filter.zone', 'zone', Join::WITH, 'zone IN (:zones)')
            ->addSelect('COUNT(ps) as nb_campaigns')
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function createReportRatioQueryBuilder(string $instanceScope, int $maxHistory): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ps')
            ->innerJoin(AdherentMessage::class, 'message', Join::WITH, 'message = ps.message')
            ->select('COALESCE(ROUND(SUM(ps.uniqueOpensEmail) / NULLIF(SUM(ps.uniqueEmails), 0), 4), 0) AS opened_rate')
            ->addSelect('COALESCE(ROUND(SUM(ps.uniqueClicksEmail) / NULLIF(SUM(ps.uniqueEmails), 0), 4), 0) AS clicked_rate')
            ->addSelect('COALESCE(ROUND(SUM(ps.unsubscribed) / NULLIF(SUM(ps.uniqueEmails), 0), 4), 0) AS unsubscribed_rate')
            ->where('message.sentAt >= :last_month')
            ->setParameter('last_month', new \DateTime("-$maxHistory days"))
        ;

        if (!ScopeEnum::isNational($instanceScope)) {
            $qb
                ->andWhere('message.instanceScope = :instance_scope')
                ->setParameter('instance_scope', $instanceScope)
            ;
        }

        return $qb;
    }
}
