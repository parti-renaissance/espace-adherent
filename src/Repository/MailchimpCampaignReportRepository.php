<?php

namespace App\Repository;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpCampaignReport;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MailchimpCampaignReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaignReport::class);
    }

    public function findNationalReportRatio(string $messageType): array
    {
        return $this->createReportRationQueryBuilder($messageType)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function findLocalReportRation(string $messageType, array $zones): array
    {
        return $this->createReportRationQueryBuilder($messageType)
            ->innerJoin(AudienceFilter::class, 'filter', Join::WITH, 'message.filter = filter')
            ->innerJoin('filter.zone', 'zone', Join::WITH, 'zone IN (:zones)')
            ->addSelect('COUNT(mc) as nb_campagnes')
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function createReportRationQueryBuilder(string $messageType): QueryBuilder
    {
        $qb = $this->createQueryBuilder('mcr')
            ->innerJoin(MailchimpCampaign::class, 'mc', Join::WITH, 'mcr = mc.report')
            ->innerJoin(AbstractAdherentMessage::class, 'message', Join::WITH, 'message = mc.message')
            ->select('COALESCE(ROUND(SUM(mcr.openUnique) / SUM(mcr.emailSent), 4), 0) AS tx_ouverture')
            ->addSelect('COALESCE(ROUND(SUM(mcr.clickUnique) / SUM(mcr.emailSent), 4), 0) AS tx_clique')
            ->addSelect('COALESCE(ROUND(SUM(mcr.unsubscribed) / SUM(mcr.emailSent), 4), 0) AS tx_desabonnement')
            ->where('message.sentAt >= :last_month')
            ->setParameter('last_month', new \DateTime('-30 days'))
        ;

        if (!AdherentMessageTypeEnum::isValid($messageType)) {
            throw new \InvalidArgumentException('Message type is invalid');
        }

        if (!\in_array($messageType, ScopeEnum::NATIONAL_SCOPES)) {
            $qb
                ->andWhere('message INSTANCE OF :type')
                ->setParameter('type', $messageType)
            ;
        }

        return $qb;
    }
}
