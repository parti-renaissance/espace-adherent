<?php

namespace App\Repository\Phoning;

use App\Entity\Adherent;
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
}
