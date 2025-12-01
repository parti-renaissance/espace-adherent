<?php

declare(strict_types=1);

namespace App\Repository\AdherentMandate;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentMandate\CommitteeAdherentMandate>
 */
class CommitteeAdherentMandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeAdherentMandate::class);
    }

    public function findActiveMandate(
        Adherent $adherent,
        ?Committee $excludedCommittee = null,
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

    public function findAllActiveMandatesForAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.adherent = :adherent')
            ->andWhere('m.finishAt IS NULL')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllActiveMandatesForCommittee(Committee $committee): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.committee = :committee')
            ->andWhere('m.finishAt IS NULL')
            ->setParameter('committee', $committee)
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasActiveMandate(Adherent $adherent): bool
    {
        return $this->count(['finishAt' => null, 'adherent' => $adherent]) > 0;
    }

    /**
     * @return CommitteeAdherentMandate[]
     */
    public function findActiveCommitteeMandates(Adherent $adherent, array $refTags = [], ?string $quality = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.committee', 'committee')
            ->where('m.adherent = :adherent AND m.finishAt IS NULL')
            ->andWhere('m.quality = :quality')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('quality', $quality)]))
        ;

        return $qb
            ->getQuery()
            ->getResult()
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

        return array_map(static function (array $mandate) {
            return $mandate['id'];
        }, $activeMandates);
    }

    public function closeMandates(
        Committee $committee,
        string $reason,
        ?\DateTime $finishAt = null,
        ?string $quality = null,
        ?string $gender = null,
    ): void {
        $qb = $this->createQueryBuilder('m')
            ->update()
            ->where('m.committee = :committee')
            ->andWhere('m.finishAt IS NULL')
            ->set('m.finishAt', ':finish_at')
            ->set('m.reason', ':reason')
            ->setParameters(new ArrayCollection([new Parameter('committee', $committee), new Parameter('finish_at', $finishAt ?? new \DateTime()), new Parameter('reason', $reason)]))
        ;

        if ($quality) {
            if (CommitteeAdherentMandateTypeEnum::TYPE_DESIGNED_ADHERENT === $quality) {
                // null value for DesignedAdherent mandate
                $qb->andWhere('m.quality IS NULL');
            } else {
                $qb
                    ->andWhere('m.quality = :quality')
                    ->setParameter('quality', $quality)
                ;
            }
        }

        if ($gender) {
            $qb
                ->andWhere('m.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        $qb->getQuery()->execute();
    }
}
