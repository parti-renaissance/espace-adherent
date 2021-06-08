<?php

namespace App\Repository;

use App\Entity\PushToken;
use Doctrine\Persistence\ManagerRegistry;

class PushTokenRepository extends EventRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushToken::class);
    }
}
