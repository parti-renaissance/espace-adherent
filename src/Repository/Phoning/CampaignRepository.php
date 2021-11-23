<?php

namespace App\Repository\Phoning;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Phoning\CampaignHistoryStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    /** @return Campaign[] */
    public function findForAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('campaign')
            ->addSelect('campaignHistory', 'dataSurvey')
            ->leftJoin('campaign.team', 'team')
            ->leftJoin('team.members', 'team_member')
            ->leftJoin(
                'campaign.campaignHistories',
                'campaignHistory',
                Join::WITH,
                'campaignHistory.caller = :adherent AND campaignHistory.status != :send')
            ->leftJoin(
                'campaignHistory.dataSurvey',
                'dataSurvey',
                Join::WITH,
                'dataSurvey.author = :adherent'
            )
            ->andWhere('(campaign.permanent = :true OR (team_member.adherent = :adherent AND campaign.finishAt > :now))')
            ->setParameters([
                'adherent' => $adherent,
                'send' => CampaignHistoryStatusEnum::SEND,
                'now' => new \DateTime(),
                'true' => true,
            ])
            ->orderBy('campaign.permanent', 'ASC')
            ->addOrderBy('campaign.finishAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPhoningCampaignsKpi()
    {
        return $this->createQueryBuilder('campaign')
            ->select('COUNT(DISTINCT campaign.id) AS nb_campaigns')
            ->addSelect('COUNT(DISTINCT IF(campaign.finishAt >= :now OR campaign.finishAt IS NULL, campaign.id, null)) AS nb_on_going_campaigns')
            ->addSelect('COUNT(campaignHistory.id) AS nb_calls')
            ->addSelect('SUM(IF(campaignHistory.beginAt >= :last_month AND campaignHistory.beginAt <= :now, 1, 0)) AS nb_calls_last_month')
            ->addSelect('SUM(IF(campaignHistory.dataSurvey IS NOT NULL, 1, 0)) as nb_surveys')
            ->addSelect('SUM(IF(dataSurvey.postedAt >= :last_month AND dataSurvey.postedAt <= :now, 1, 0)) as nb_surveys_last_month')
            ->leftJoin(
                'campaign.campaignHistories',
                'campaignHistory',
                Join::WITH,
                'campaignHistory.status != :send'
            )
            ->leftJoin('campaignHistory.dataSurvey', 'dataSurvey')
            ->setParameters([
                'now' => new \DateTime(),
                'last_month' => new \DateTime('last month'),
                'send' => CampaignHistoryStatusEnum::SEND,
            ])
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
