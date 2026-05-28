<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class AdherentActivationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentActivationCode::class);
    }

    public function findOneActiveByCode(string $code, Adherent $adherent): ?AdherentActivationCode
    {
        return $this->createQueryBuilder('code')
            ->where('code.adherent = :adherent')
            ->andWhere('code.value = :code')
            ->andWhere('code.revokedAt IS NULL')
            ->andWhere('code.usedAt IS NULL')
            ->setParameters(new ArrayCollection([
                new Parameter('adherent', $adherent),
                new Parameter('code', $code),
            ]))
            ->orderBy('code.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return AdherentActivationCode[]
     */
    public function findRecentByAdherent(Adherent $adherent, \DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('code')
            ->where('code.adherent = :adherent')
            ->andWhere('code.createdAt >= :since')
            ->setParameters(new ArrayCollection([
                new Parameter('adherent', $adherent),
                new Parameter('since', $since),
            ]))
            ->orderBy('code.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLatestActive(Adherent $adherent): ?AdherentActivationCode
    {
        return $this->createQueryBuilder('code')
            ->where('code.adherent = :adherent')
            ->andWhere('code.revokedAt IS NULL')
            ->andWhere('code.usedAt IS NULL')
            ->setParameters(new ArrayCollection([
                new Parameter('adherent', $adherent),
            ]))
            ->orderBy('code.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function invalidateForAdherent(Adherent $adherent): void
    {
        $this->createQueryBuilder('code')
            ->update()
            ->where('code.adherent = :adherent')
            ->andWhere('code.usedAt IS NULL AND code.revokedAt IS NULL')
            ->set('code.revokedAt', ':now')
            ->setParameters(new ArrayCollection([
                new Parameter('adherent', $adherent),
                new Parameter('now', new \DateTime()),
            ]))
            ->getQuery()
            ->execute()
        ;
    }

    public function incrementFailedAttempts(AdherentActivationCode $code, int $maxAttempts): void
    {
        $this->createQueryBuilder('code')
            ->update()
            ->set('code.failedAttempts', 'code.failedAttempts + 1')
            ->set('code.revokedAt', 'CASE WHEN code.failedAttempts >= :max THEN :now ELSE code.revokedAt END')
            ->where('code.id = :id')
            ->setParameters(new ArrayCollection([
                new Parameter('max', $maxAttempts),
                new Parameter('now', new \DateTime()),
                new Parameter('id', $code->getId()),
            ]))
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Atomic compare-and-set consume: marks the code as used only if it is still active.
     * Returns the number of affected rows — 0 means another concurrent caller consumed it first.
     */
    public function markAsUsedIfActive(AdherentActivationCode $code): int
    {
        return (int) $this->createQueryBuilder('code')
            ->update()
            ->set('code.usedAt', ':now')
            ->where('code.id = :id')
            ->andWhere('code.usedAt IS NULL')
            ->andWhere('code.revokedAt IS NULL')
            ->setParameters(new ArrayCollection([
                new Parameter('now', new \DateTime()),
                new Parameter('id', $code->getId()),
            ]))
            ->getQuery()
            ->execute()
        ;
    }
}
