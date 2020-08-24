<?php

namespace App\Repository\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeAdherentMandate::class);
    }

    public function findActiveMandate(
        Adherent $adherent,
        Committee $excludedCommittee = null
    ): ?CommitteeAdherentMandate {
        $qb = $this->createQueryBuilder('m')
            ->where('m.adherent = :adherent')
            ->andWhere('m.finishAt IS NULL')
            ->setParameter('adherent', $adherent)
        ;

        if ($excludedCommittee) {
            $qb
                ->andWhere('m.committee != :committee')
                ->setParameter('committee', $excludedCommittee)
            ;
        }

        return $qb->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findActiveMandateFor(Adherent $adherent, Committee $committee): ?CommitteeAdherentMandate
    {
        return $this->createQueryBuilder('m')
            ->where('m.adherent = :adherent')
            ->andWhere('m.committee = :committee')
            ->andWhere('m.finishAt IS NULL')
            ->setParameter('adherent', $adherent)
            ->setParameter('committee', $committee)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findActiveMandateAdherentIds(Committee $committee): array
    {
        $activeMandates = $this->createQueryBuilder('mandate')
            ->select('adherent.id')
            ->leftJoin('mandate.adherent', 'adherent')
            ->where('mandate.committee = :committee')
            ->andWhere('mandate.finishAt IS NULL')
            ->setParameter('committee', $committee)
            ->getQuery()
            ->getScalarResult()
        ;

        return \array_map(static function (array $mandate) {
            return $mandate['id'];
        }, $activeMandates);
    }
}
