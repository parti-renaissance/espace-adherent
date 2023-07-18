<?php

namespace App\Repository\AdherentMandate;

use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ElectedRepresentativeAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedRepresentativeAdherentMandate::class);
    }
}
