<?php

declare(strict_types=1);

namespace App\Repository\Phoning;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Entity\Phoning\CampaignHistory;
use App\Phoning\CampaignHistoryStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CampaignHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampaignHistory::class);
    }

    public function findLastHistoryForAdherent(Adherent $adherent): ?CampaignHistory
    {
        return $this->createQueryBuilder('campaignHistory')
            ->where('campaignHistory.adherent = :adherent AND campaignHistory.status != :send')
            ->setParameters([
                'adherent' => $adherent,
                'send' => CampaignHistoryStatusEnum::SEND,
            ])
            ->orderBy('campaignHistory.beginAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findPhoningCampaignAverageCallingTime(Campaign $campaign): int
    {
        return (int) $this->createQueryBuilder('campaignHistory')
            ->select('ABS(AVG(TIMESTAMPDIFF(SECOND, campaignHistory.finishAt, campaignHistory.beginAt))) AS average_calling_time')
            ->where('campaignHistory.campaign = :campaign AND campaignHistory.status != :send')
            ->setParameters([
                'campaign' => $campaign,
                'send' => CampaignHistoryStatusEnum::SEND,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countPhoningCampaignAdherentsCalled(Campaign $campaign): int
    {
        return (int) $this->createQueryBuilder('campaignHistory')
            ->select('COUNT(DISTINCT campaignHistory.adherent)')
            ->where('campaignHistory.campaign = :campaign AND campaignHistory.status != :send')
            ->setParameters([
                'campaign' => $campaign,
                'send' => CampaignHistoryStatusEnum::SEND,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
