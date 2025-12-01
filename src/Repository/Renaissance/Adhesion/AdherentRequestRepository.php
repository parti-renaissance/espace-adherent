<?php

declare(strict_types=1);

namespace App\Repository\Renaissance\Adhesion;

use App\Adhesion\AdherentRequestReminderTypeEnum;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Entity\Renaissance\Adhesion\AdherentRequestReminder;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Renaissance\Adhesion\AdherentRequest>
 */
class AdherentRequestRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentRequest::class);
    }

    /** @return AdherentRequest[] */
    public function findToRemind(
        AdherentRequestReminderTypeEnum $type,
        \DateTimeInterface $createdBefore,
        ?\DateTimeInterface $createdAfter = null,
    ): array {
        $qb = $this->createQueryBuilder('adherent_request')
            ->select('PARTIAL adherent_request.{id, uuid}')
            ->leftJoin(
                AdherentRequestReminder::class,
                'reminder',
                Join::WITH,
                'adherent_request.id = reminder.adherentRequest AND reminder.type = :reminder_type'
            )
            ->andWhere('reminder.id IS NULL')
            ->andWhere('adherent_request.createdAt <= :created_before')
            ->andWhere('adherent_request.adherent IS NULL')
            ->andWhere('adherent_request.accountCreatedAt IS NULL')
            ->andWhere('adherent_request.email IS NOT NULL')
            ->setParameters(new ArrayCollection([new Parameter('reminder_type', $type), new Parameter('created_before', $createdBefore)]))
        ;

        if ($createdAfter) {
            $qb
                ->andWhere('adherent_request.createdAt >= :created_after')
                ->setParameter('created_after', $createdAfter)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
