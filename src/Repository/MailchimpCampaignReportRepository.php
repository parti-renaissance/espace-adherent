<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpCampaignReport;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentMessage\MailchimpCampaignReport>
 */
class MailchimpCampaignReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaignReport::class);
    }

    public function findNationalReportRatio(string $instanceScope, int $maxHistory = 30): array
    {
        return $this->createReportRationQueryBuilder($instanceScope, $maxHistory)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function findLocalReportRation(string $instanceScope, array $zones, int $maxHistory = 30): array
    {
        return $this->createReportRationQueryBuilder($instanceScope, $maxHistory)
            ->innerJoin(AudienceFilter::class, 'filter', Join::WITH, 'message.filter = filter')
            ->innerJoin('filter.zone', 'zone', Join::WITH, 'zone IN (:zones)')
            ->addSelect('COUNT(mc) as nb_campaigns')
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function createReportRationQueryBuilder(string $instanceScope, int $maxHistory): QueryBuilder
    {
        $qb = $this->createQueryBuilder('mcr')
            ->innerJoin(MailchimpCampaign::class, 'mc', Join::WITH, 'mcr = mc.report')
            ->innerJoin(AdherentMessage::class, 'message', Join::WITH, 'message = mc.message')
            ->select('COALESCE(ROUND(SUM(mcr.openUnique) / SUM(mcr.emailSent), 4), 0) AS opened_rate')
            ->addSelect('COALESCE(ROUND(SUM(mcr.clickUnique) / SUM(mcr.emailSent), 4), 0) AS clicked_rate')
            ->addSelect('COALESCE(ROUND(SUM(mcr.unsubscribed) / SUM(mcr.emailSent), 4), 0) AS unsubscribed_rate')
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
