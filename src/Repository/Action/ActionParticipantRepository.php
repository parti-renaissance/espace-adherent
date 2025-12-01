<?php

declare(strict_types=1);

namespace App\Repository\Action;

use App\Entity\Action\ActionParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Action\ActionParticipant>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('action_uuid', $actionUuid), new Parameter('adherent_uuid', $adherentUuid)]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllRegistrationDates(string $adherentUuid): array
    {
        return array_column($this->createQueryBuilder('ap')
            ->select('ap.createdAt', 'a.id')
            ->innerJoin('ap.adherent', 'ad')
            ->innerJoin('ap.action', 'a')
            ->where('ad.uuid = :adherent_uuid')
            ->setParameter('adherent_uuid', $adherentUuid)
            ->getQuery()
            ->getResult(), 'createdAt', 'id');
    }
}
