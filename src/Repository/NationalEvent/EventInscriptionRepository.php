<?php

namespace App\Repository\NationalEvent;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventInscriptionRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventInscription::class);
    }

    /**
     * @return EventInscription[]
     */
    public function findAllForTags(Adherent $adherent): array
    {
        return $this->createQueryBuilder('ei')
            ->addSelect('e')
            ->innerJoin('ei.event', 'e')
            ->where('ei.adherent = :adherent')
            ->andWhere('e.startDate >= :start_date')
            ->setParameter('adherent', $adherent)
            ->setParameter('start_date', new \DateTime('-6 months'))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return EventInscription[]
     */
    public function findAllForAdherentAndEvent(Adherent $adherent, NationalEvent $event, string $excludedStatus): array
    {
        return $this->createQueryBuilder('ei')
            ->addSelect('CASE WHEN ei.status = :status_accepted THEN 1
                WHEN ei.status = :status_inconclusive THEN 2
                ELSE 3 END AS HIDDEN score')
            ->where('ei.adherent = :adherent')
            ->andWhere('ei.event = :event')
            ->andWhere('ei.status != :excluded_status')
            ->setParameters([
                'adherent' => $adherent,
                'event' => $event,
                'excluded_status' => $excludedStatus,
                'status_accepted' => InscriptionStatusEnum::ACCEPTED,
                'status_inconclusive' => InscriptionStatusEnum::INCONCLUSIVE,
            ])
            ->orderBy('score', 'ASC')
            ->addOrderBy('ei.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return EventInscription[]
     */
    public function findAcceptedByEmail(string $email, ?NationalEvent $excludedEvent = null): array
    {
        $qb = $this->createQueryBuilder('ei')
            ->where('ei.addressEmail = :email')
            ->andWhere('ei.status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', InscriptionStatusEnum::ACCEPTED)
        ;

        if ($excludedEvent) {
            $qb
                ->andWhere('ei.event != :excluded_event')
                ->setParameter('excluded_event', $excludedEvent)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllForEventPaginated(NationalEvent $event, ?string $searchTerm, array $statuses, int $page = 1, $limit = 30): PaginatorInterface
    {
        $queryBuilder = $this->createQueryBuilder('ei')
            ->addSelect('adherent')
            ->leftJoin('ei.adherent', 'adherent')
            ->where('ei.event = :event')
            ->andWhere('ei.status IN (:statuses)')
            ->setParameter('event', $event)
            ->setParameter('statuses', $statuses)
            ->orderBy('ei.createdAt', 'DESC')
        ;

        if ($searchTerm) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('ei.firstName', ':searchTerm'),
                    $queryBuilder->expr()->like('ei.lastName', ':searchTerm'),
                    $queryBuilder->expr()->like('ei.addressEmail', ':searchTerm'),
                    $queryBuilder->expr()->like('ei.uuid', ':searchTerm'),
                ))
                ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ;
        }

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    /**
     * @return EventInscription[]
     */
    public function findAllPartialForEvent(NationalEvent $event, array $statuses, bool $withoutTicket = false): array
    {
        $qb = $this->createQueryBuilder('ei')
            ->select('PARTIAL ei.{id, uuid}')
             ->where('ei.event = :event')
             ->andWhere('ei.status IN (:statuses)')
             ->setParameter('event', $event)
             ->setParameter('statuses', $statuses)
        ;

        if ($withoutTicket) {
            $qb->andWhere('ei.ticketSentAt IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    public function countWithoutTicketQRCodes(NationalEvent $event): int
    {
        return $this->createQueryBuilder('ei')
            ->select('COUNT(ei)')
            ->where('ei.event = :event')
            ->andWhere('ei.ticketQRCodeFile IS NULL')
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countTickets(NationalEvent $event, bool $withoutTicket, array $statuses): int
    {
        return $this->createQueryBuilder('ei')
            ->select('COUNT(ei)')
            ->where('ei.event = :event')
            ->andWhere('ei.ticketSentAt IS '.($withoutTicket ? '' : 'NOT').' NULL')
            ->andWhere('ei.status IN (:statuses)')
            ->setParameter('event', $event)
            ->setParameter('statuses', $statuses)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return EventInscription[]
     */
    public function findAllWithoutTickets(NationalEvent $event): array
    {
        return $this->createQueryBuilder('ei')
            ->select('PARTIAL ei.{id, uuid}')
            ->where('ei.event = :event')
            ->andWhere('ei.ticketQRCodeFile IS NULL')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByStatus(NationalEvent $event): array
    {
        return array_column($this->createQueryBuilder('ei', 'ei.status')
            ->select('ei.status, COUNT(ei) as count')
            ->where('ei.event = :event')
            ->groupBy('ei.status')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult(),
            'count',
            'status');
    }

    public function findOneForAdherent(Adherent $adherent, NationalEvent $event): ?EventInscription
    {
        return $this->createQueryBuilder('ei')
            ->where('ei.adherent = :adherent')
            ->andWhere('ei.event = :event')
            ->setParameter('adherent', $adherent)
            ->setParameter('event', $event)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findDuplicate(EventInscription $eventInscription): ?EventInscription
    {
        return $this->createQueryBuilder('ei')
            ->where('ei.id != :event_inscription_id')
            ->andWhere('ei.event = :event')
            ->andWhere('ei.addressEmail = :email')
            ->andWhere('ei.firstName = :firstName')
            ->andWhere('ei.lastName = :lastName')
            ->andWhere('ei.status != :status')
            ->orderBy('ei.createdAt', 'ASC')
            ->setParameters([
                'event_inscription_id' => $eventInscription->getId(),
                'event' => $eventInscription->event,
                'email' => $eventInscription->addressEmail,
                'firstName' => $eventInscription->firstName,
                'lastName' => $eventInscription->lastName,
                'status' => InscriptionStatusEnum::DUPLICATE,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
