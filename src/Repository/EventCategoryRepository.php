<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event\EventCategory;
use Doctrine\Persistence\ManagerRegistry;

class EventCategoryRepository extends BaseEventCategoryRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCategory::class);
    }
}
