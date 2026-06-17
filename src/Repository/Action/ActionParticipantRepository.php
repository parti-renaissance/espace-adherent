<?php

declare(strict_types=1);

namespace App\Repository\Action;

use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use App\Entity\Adherent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
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
            ->setParameters(new ArrayCollection([
                new Parameter('action_uuid', $actionUuid),
                new Parameter('adherent_uuid', $adherentUuid),
            ]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Adherent[]
     */
    public function findParticipantAdherents(Action $action): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('PARTIAL ad.{id, emailAddress, firstName, lastName}')
            ->from(Adherent::class, 'ad')
            ->innerJoin(ActionParticipant::class, 'ap', Join::WITH, 'ap.adherent = ad')
            ->where('ap.action = :action')
            ->andWhere('ad.status = :status')
            ->setParameters(new ArrayCollection([
                new Parameter('action', $action),
                new Parameter('status', Adherent::ENABLED),
            ]))
            ->getQuery()
            ->getResult()
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
