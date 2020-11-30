<?php

namespace App\Repository;

use App\Entity\AdherentResetPasswordToken;
use Doctrine\Common\Persistence\ManagerRegistry;

class AdherentResetPasswordTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentResetPasswordToken::class);
    }
}
