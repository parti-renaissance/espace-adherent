<?php

namespace App\Repository\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TerritorialCouncilAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function closeMandates(TerritorialCouncil $territorialCouncil, \DateTime $finishAt, array $qualities): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->where('m.territorialCouncil = :territorial_council')
            ->andWhere('m.quality IN (:qualities)')
            ->andWhere('m.finishAt IS NULL')
            ->set('m.finishAt', ':finish_at')
            ->setParameters([
                'territorial_council' => $territorialCouncil,
                'qualities' => $qualities,
                'finish_at' => $finishAt,
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
