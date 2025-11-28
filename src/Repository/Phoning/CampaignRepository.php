<?php

declare(strict_types=1);

namespace App\Repository\Phoning;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Phoning\CampaignHistoryStatusEnum;
use App\Repository\ScopeVisibilityEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CampaignRepository extends ServiceEntityRepository
{
    use ScopeVisibilityEntityRepositoryTrait;

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

    public function findPhoningCampaignsKpi(array $zones = []): array
    {
        $queryBuilder = $this->createQueryBuilder('campaign')
            ->select('COUNT(DISTINCT campaign.id) AS nb_campaigns')
            ->addSelect('COUNT(DISTINCT IF(campaign.finishAt >= :now OR campaign.finishAt IS NULL, campaign.id, null)) AS nb_ongoing_campaigns')
            ->addSelect('COUNT(campaignHistory.id) AS nb_calls')
            ->addSelect('COUNT(IF(campaignHistory.beginAt >= :last_30d AND campaignHistory.beginAt <= :now, campaignHistory.id, null)) AS nb_calls_last_30d')
            ->addSelect('COUNT(campaignHistory.dataSurvey) as nb_surveys')
            ->addSelect('COUNT(IF(dataSurvey.postedAt >= :last_30d AND dataSurvey.postedAt <= :now, dataSurvey.id, null)) as nb_surveys_last_30d')
            ->leftJoin(
                'campaign.campaignHistories',
                'campaignHistory',
                Join::WITH,
                'campaignHistory.status != :send'
            )
            ->leftJoin('campaignHistory.dataSurvey', 'dataSurvey')
            ->setParameters([
                'now' => new \DateTime(),
                'last_30d' => new \DateTime('-30 days'),
                'send' => CampaignHistoryStatusEnum::SEND,
            ])
        ;

        $this->addScopeVisibility($queryBuilder, $zones);

        return $queryBuilder
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
