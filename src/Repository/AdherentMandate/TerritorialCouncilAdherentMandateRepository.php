<?php

namespace App\Repository\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncilAdherentMandate::class);
    }

    public function findActiveMandateWithQuality(
        Adherent $adherent,
        TerritorialCouncil $territorialCouncil,
        string $quality
    ): ?TerritorialCouncilAdherentMandate {
        return $this->createQueryBuilder('m')
            ->where('m.adherent = :adherent')
            ->andWhere('m.territorialCouncil = :territorialCouncil')
            ->andWhere('m.quality = :quality')
            ->andWhere('m.finishAt IS NULL')
            ->setParameter('adherent', $adherent)
            ->setParameter('territorialCouncil', $territorialCouncil)
            ->setParameter('quality', $quality)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
