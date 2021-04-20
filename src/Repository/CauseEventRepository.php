<?php

namespace App\Repository;

use App\Entity\Event\CauseEvent;
use Doctrine\Persistence\ManagerRegistry;

class CauseEventRepository extends EventRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CauseEvent::class);
    }
}
