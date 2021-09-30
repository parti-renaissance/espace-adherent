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
            ->andWhere('(campaign.permanent = true OR (team_member.adherent = :adherent AND campaign.finishAt > :now))')
            ->setParameters([
                'adherent' => $adherent,
                'send' => CampaignHistoryStatusEnum::SEND,
                'now' => new \DateTime(),
            ])
            ->orderBy('campaign.permanent', 'ASC')
            ->addOrderBy('campaign.finishAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
