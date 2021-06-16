<?php

namespace App\Repository\AdherentMandate;

use App\Entity\AdherentMandate\NationalCouncilAdherentMandate;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NationalCouncilAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NationalCouncilAdherentMandate::class);
    }

    public function closeMandates(TerritorialCouncil $territorialCouncil, \DateTime $finishAt): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->where('m.territorialCouncil = :territorial_council')
            ->andWhere('m.finishAt IS NULL')
            ->set('m.finishAt', ':finish_at')
            ->setParameters([
                'territorial_council' => $territorialCouncil,
                'finish_at' => $finishAt,
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
