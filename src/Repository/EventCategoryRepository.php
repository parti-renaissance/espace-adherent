<?php

namespace App\Repository;

use App\Entity\Event\EventCategory;
use App\Entity\Event\EventGroupCategory;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EventCategoryRepository extends BaseEventCategoryRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCategory::class);
    }

    public function createQueryForAllEnabledOrderedByName(?EventGroupCategory $groupCategory): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ec')
                ->join('ec.eventGroupCategory', 'egc')
                ->where('ec.status = :status')
                ->andWhere('egc.status = :status')
                ->orderBy('ec.eventGroupCategory', 'ASC')
                ->addOrderBy('ec.name', 'ASC')
                ->setParameters([
                    'status' => EventCategory::ENABLED,
                ])
        ;

        if ($groupCategory) {
            $qb
                ->andWhere('ec.eventGroupCategory = :groupCategory')
                ->setParameter('groupCategory', $groupCategory)
            ;
        }

        return $qb;
    }
}
