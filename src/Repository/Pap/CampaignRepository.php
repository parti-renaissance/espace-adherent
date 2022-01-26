<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Campaign;
use App\Repository\ScopeVisibilityEntityRepositoryTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CampaignRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use ScopeVisibilityEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    public function findCampaignsKpi(array $zones = []): array
    {
        $queryBuilder = $this->createQueryBuilder('campaign')
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
        ;

        $this->addScopeVisibility($queryBuilder, $zones);

        return $queryBuilder
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function countActiveCampaign(): int
    {
        return $this->createQueryBuilder('campaign')
            ->select('COUNT(1)')
            ->where('campaign.finishAt IS NOT NULL AND campaign.finishAt > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findActiveCampaignsVotePlaceIds(): array
    {
        $activeCampaign = $this->createQueryBuilder('campaign')
            ->innerJoin('campaign.votePlaces', 'votePlace')
            ->where('campaign.finishAt IS NOT NULL AND campaign.finishAt > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleResult()
        ;

        $queryBuilder = $this->createQueryBuilder('campaign')
            ->select('DISTINCT votePlace.id')
            ->innerJoin('campaign.votePlaces', 'votePlace')
            ->where('campaign.finishAt IS NOT NULL AND campaign.finishAt > :now')
            ->setParameter('now', new \DateTime())
        ;

        if ($activeCampaign) {
            if ($deltaPredictionAndResultMin2017 = $activeCampaign->getDeltaPredictionAndResultMin2017()) {
                $queryBuilder->andWhere(sprintf('votePlace.deltaPredictionAndResult2017 >= %s', $deltaPredictionAndResultMin2017));
            }

            if ($deltaPredictionAndResultMax2017 = $activeCampaign->getDeltaPredictionAndResultMax2017()) {
                $queryBuilder->andWhere(sprintf('votePlace.deltaPredictionAndResult2017 <= %s', $deltaPredictionAndResultMax2017));
            }

            if ($deltaAveragePredictionsMin = $activeCampaign->getDeltaAveragePredictionsMin()) {
                $queryBuilder->andWhere(sprintf('votePlace.deltaAveragePredictions >= %s', $deltaAveragePredictionsMin));
            }

            if ($deltaAveragePredictionsMax = $activeCampaign->getDeltaAveragePredictionsMax()) {
                $queryBuilder->andWhere(sprintf('votePlace.deltaAveragePredictions <= %s', $deltaAveragePredictionsMax));
            }

            if ($abstentionsMin2017 = $activeCampaign->getAbstentionsMin2017()) {
                $queryBuilder->andWhere(sprintf('votePlace.abstentions2017 >= %s', $abstentionsMin2017));
            }

            if ($abstentionsMax2017 = $activeCampaign->getAbstentionsMax2017()) {
                $queryBuilder->andWhere(sprintf('votePlace.abstentions2017 <= %s', $abstentionsMax2017));
            }

            if ($misregistrationsPriorityMin = $activeCampaign->getMisregistrationsPriorityMin()) {
                $queryBuilder->andWhere(sprintf('votePlace.misregistrationsPriority >= %s', $misregistrationsPriorityMin));
            }

            if ($misregistrationsPriorityMax = $activeCampaign->getMisregistrationsPriorityMax()) {
                $queryBuilder->andWhere(sprintf('votePlace.misregistrationsPriority <= %s', $misregistrationsPriorityMax));
            }
        }

        return array_column(
            $queryBuilder
                ->getQuery()
                ->getArrayResult(),
            'id'
        );
    }
}
