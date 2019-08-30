<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\MunicipalEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MunicipalEventRepository extends EventRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MunicipalEvent::class);
    }

    public function countEventForOrganizer(Adherent $organizer): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(1)')
            ->where('e.status = :status')
            ->andWhere('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->setParameter('status', Event::STATUS_SCHEDULED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getAllCategories(): array
    {
        $results = $this->createQueryBuilder('event')
            ->select('DISTINCT category.name')
            ->innerJoin('event.category', 'category')
            ->where('event.status = :scheduled')
            ->andWhere('event.finishAt > :now')
            ->setParameter('scheduled', MunicipalEvent::STATUS_SCHEDULED)
            ->setParameter('now', new \DateTime())
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return \array_column($results, 'name');
    }
}
