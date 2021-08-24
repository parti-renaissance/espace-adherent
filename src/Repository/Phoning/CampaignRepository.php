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

    public function findForAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('campaign')
            ->leftJoin('campaign.team', 'team')
            ->leftJoin('team.members', 'tmember')
            ->where('campaign.finishAt > :now')
            ->andWhere('tmember.adherent = :adherent')
            ->setParameters([
                'adherent' => $adherent,
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
