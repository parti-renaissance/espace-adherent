<?php

namespace App\Repository;

use App\Entity\AdherentEmailSubscribeToken;
use Doctrine\Persistence\ManagerRegistry;

class AdherentEmailSubscribeTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentEmailSubscribeToken::class);
    }
}
