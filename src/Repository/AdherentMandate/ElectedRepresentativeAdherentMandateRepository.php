<?php

namespace App\Repository\AdherentMandate;

use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class ElectedRepresentativeAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedRepresentativeAdherentMandate::class);
    }

    public function findMandatesForAdherentId(int $adherentId): ArrayCollection
    {
        $mandates = $this->createQueryBuilder('mandate')
            ->andWhere('mandate.adherent = :adherent')
            ->setParameter('adherent', $adherentId)
            ->getQuery()
            ->getResult()
        ;

        return new ArrayCollection($mandates);
    }
}
