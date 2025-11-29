<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentActivationToken;
use Doctrine\Persistence\ManagerRegistry;

class AdherentActivationTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentActivationToken::class);
    }
}
