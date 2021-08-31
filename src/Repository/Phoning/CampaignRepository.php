<?php

namespace App\Repository\Phoning;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->innerJoin('campaign.team', 'team')
            ->innerJoin('team.members', 'team_member')
            ->where('campaign.finishAt > :now')
            ->andWhere('team_member.adherent = :adherent')
            ->setParameters([
                'adherent' => $adherent,
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
