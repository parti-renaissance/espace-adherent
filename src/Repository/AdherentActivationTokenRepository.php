<?php

namespace App\Repository;

use App\Entity\AdherentActivationToken;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AdherentActivationTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AdherentActivationToken::class);
    }
}
