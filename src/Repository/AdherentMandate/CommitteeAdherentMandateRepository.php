<?php

namespace App\Repository\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommitteeAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function hasActiveMandate(Adherent $adherent): bool
    {
        return $this->count(['finishAt' => null, 'adherent' => $adherent]) > 0;
    }

    public function findActiveMandateInTerritorialCouncil(
        Adherent $adherent,
        TerritorialCouncil $territorialCouncil
    ): ?CommitteeAdherentMandate {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.committee', 'committee')
            ->leftJoin('committee.referentTags', 'tag')
            ->where('m.adherent = :adherent')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('tags', $territorialCouncil->getReferentTags())
            ->andWhere('m.finishAt IS NULL')
            ->setParameter('adherent', $adherent)
            ->getQuery()
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
            ->andWhere('mandate.finishAt IS NULL AND mandate.quality IS NULL')
            ->setParameter('committee', $committee)
            ->getQuery()
            ->getScalarResult()
        ;

        return \array_map(static function (array $mandate) {
            return $mandate['id'];
        }, $activeMandates);
    }

    public function closeCommitteeMandate(Committee $committee, string $reason, \DateTime $finishAt = null): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->where('m.committee = :committee')
            ->andWhere('m.finishAt IS NULL')
            ->set('m.finishAt', ':finish_at')
            ->set('m.reason', ':reason')
            ->setParameters([
                'committee' => $committee,
                'finish_at' => $finishAt ?? new \DateTime(),
                'reason' => $reason,
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
