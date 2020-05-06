<?php

namespace App\Repository;

use App\Entity\AdherentResetPasswordToken;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AdherentResetPasswordTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AdherentResetPasswordToken::class);
    }
}
