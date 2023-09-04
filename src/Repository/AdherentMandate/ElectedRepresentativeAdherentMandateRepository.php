<?php

namespace App\Repository\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ElectedRepresentativeAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedRepresentativeAdherentMandate::class);
    }

    public function getAdherentMandateTypes(Adherent $adherent): array
    {
        $mandateTypes = $this
            ->createQueryBuilder('mandate')
            ->select('DISTINCT(mandate.mandateType)')
            ->andWhere('mandate.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->andWhere('mandate.finishAt IS NULL')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map('current', $mandateTypes);
    }
}
