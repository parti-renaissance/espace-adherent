<?php

namespace App\Repository\Action;

use App\Entity\Action\ActionParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActionParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionParticipant::class);
    }

    public function findAdherentRegistration(string $actionUuid, string $adherentUuid): ?ActionParticipant
    {
        return $this->createQueryBuilder('ap')
            ->innerJoin('ap.action', 'a')
            ->innerJoin('ap.adherent', 'ad')
            ->where('a.uuid = :action_uuid AND ad.uuid = :adherent_uuid')
            ->setParameters([
                'action_uuid' => $actionUuid,
                'adherent_uuid' => $adherentUuid,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
