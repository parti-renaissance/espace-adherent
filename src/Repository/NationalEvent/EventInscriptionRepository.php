<?php

namespace App\Repository\NationalEvent;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
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
    public function findAllByAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('ei')
            ->addSelect('e')
            ->innerJoin('ei.event', 'e')
            ->where('ei.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
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
}
