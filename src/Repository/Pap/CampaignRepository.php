<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Campaign;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CampaignRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    public function findCampaignsKpi(): array
    {
        return $this->createQueryBuilder('campaign')
            ->select('COUNT(DISTINCT campaign.id) AS nb_campaigns')
            ->addSelect('COUNT(DISTINCT IF(campaign.finishAt >= :now OR campaign.finishAt IS NULL, campaign.id, null)) AS nb_ongoing_campaigns')
            ->addSelect('COUNT(campaignHistory.id) AS nb_visited_doors')
            ->addSelect('COUNT(IF(campaignHistory.createdAt >= :last_30d AND campaignHistory.createdAt <= :now, campaignHistory.id, null)) AS nb_visited_doors_last_30d')
            ->addSelect('COUNT(campaignHistory.dataSurvey) as nb_surveys')
            ->addSelect('COUNT(IF(dataSurvey.postedAt >= :last_30d AND dataSurvey.postedAt <= :now, dataSurvey.id, null)) as nb_surveys_last_30d')
            ->leftJoin(
                'campaign.campaignHistories',
                'campaignHistory',
                Join::WITH,
                'campaignHistory.door IS NOT NULL'
            )
            ->leftJoin('campaignHistory.dataSurvey', 'dataSurvey')
            ->setParameters([
                'now' => new \DateTime(),
                'last_30d' => new \DateTime('-30 days'),
            ])
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
