<?php

namespace App\Repository\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\AbstractAdherentMandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AbstractAdherentMandate::class);
    }

    public function hasActiveMandate(Adherent $adherent): bool
    {
        return $this->count(['finishAt' => null, 'adherent' => $adherent]) > 0;
    }
}
