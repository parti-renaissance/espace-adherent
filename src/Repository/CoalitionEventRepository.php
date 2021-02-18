<?php

namespace App\Repository;

use App\Entity\Event\CoalitionEvent;
use Doctrine\Persistence\ManagerRegistry;

class CoalitionEventRepository extends EventRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoalitionEvent::class);
    }
}
