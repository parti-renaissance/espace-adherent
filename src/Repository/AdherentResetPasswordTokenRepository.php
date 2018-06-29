<?php

namespace AppBundle\Repository;

use AppBundle\Entity\AdherentResetPasswordToken;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AdherentResetPasswordTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AdherentResetPasswordToken::class);
    }
}
