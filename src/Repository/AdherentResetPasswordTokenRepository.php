<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentResetPasswordToken;
use Doctrine\Persistence\ManagerRegistry;

class AdherentResetPasswordTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentResetPasswordToken::class);
    }
}
