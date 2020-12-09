<?php

namespace App\Repository\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\AbstractAdherentMandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractAdherentMandate::class);
    }

    public function hasActiveMandate(Adherent $adherent): bool
    {
        return $this->count(['finishAt' => null, 'adherent' => $adherent]) > 0;
    }
}
