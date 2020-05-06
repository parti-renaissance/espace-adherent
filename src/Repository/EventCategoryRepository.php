<?php

namespace App\Repository;

use App\Entity\EventCategory;

class EventCategoryRepository extends BaseEventCategoryRepository
{
    public function createQueryForAllEnabledOrderedByName(): \Doctrine\ORM\QueryBuilder
    {
        return $this
            ->createQueryBuilder('ec')
                ->join('ec.eventGroupCategory', 'egc')
                ->where('ec.status = :status')
                ->andWhere('egc.status = :status')
                ->orderBy('ec.eventGroupCategory', 'ASC')
                ->addOrderBy('ec.name', 'ASC')
                ->setParameters([
                    'status' => EventCategory::ENABLED,
                ])
        ;
    }
}
