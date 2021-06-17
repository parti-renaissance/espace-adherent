<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\CoalitionEvent;
use Doctrine\Persistence\ManagerRegistry;

class CoalitionEventRepository extends EventRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoalitionEvent::class);
    }

    /**
     * @return CoalitionEvent[]|PaginatorInterface
     */
    public function findEventsByOrganizerPaginator(
        Adherent $organizer,
        int $page = 1,
        int $limit = 50
    ): PaginatorInterface {
        $qb = $this->createQueryBuilder('event')
            ->select('event', 'organizer')
            ->leftJoin('event.organizer', 'organizer')
            ->where('organizer = :organizer')
            ->orderBy('event.beginAt', 'DESC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('organizer', $organizer)
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }
}
