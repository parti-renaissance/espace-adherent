<?php

namespace App\Repository;

use App\Adherent\Referral\StatusEnum;
use App\Entity\Adherent;
use App\Entity\Referral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReferralRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referral::class);
    }

    public function isEmailReported(string $email): bool
    {
        return 0 < $this->createQueryBuilder('referral')
            ->select('COUNT(referral.id)')
            ->where('referral.emailHash = :hash')
            ->andWhere('referral.status = :status_reported')
            ->setParameters([
                'hash' => Referral::createHash($email),
                'status_reported' => StatusEnum::REPORTED,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function finishReferralAdhesionStatus(Adherent $adherent): void
    {
        $this->createQueryBuilder('r')
            ->update()
            ->set('r.status', ':new_status')
            ->set('r.updatedAt', ':date')
            ->andWhere('r.status IN (:status_to_update)')
            ->andWhere('r.referred = :referred')
            ->setParameters([
                'new_status' => StatusEnum::ADHESION_FINISHED,
                'date' => new \DateTimeImmutable(),
                'status_to_update' => [StatusEnum::ACCOUNT_CREATED, StatusEnum::INVITATION_SENT],
                'referred' => $adherent,
            ])
            ->getQuery()
            ->execute()
        ;
    }

    public function updateReferralsStatus(Adherent $adherent, ?Referral $excludeReferral, StatusEnum $status): void
    {
        $qb = $this->createQueryBuilder('r')
            ->update()
            ->set('r.status', ':new_status')
            ->set('r.updatedAt', ':date')
            ->where('r.status = :status_to_update')
            ->andWhere('r.emailAddress = :email')
            ->setParameters([
                'new_status' => $status,
                'date' => new \DateTimeImmutable(),
                'status_to_update' => StatusEnum::INVITATION_SENT,
                'email' => $adherent->getEmailAddress(),
            ])
        ;

        if ($excludeReferral) {
            $qb
                ->andWhere('r.id != :id')
                ->setParameter('id', $excludeReferral->getId())
            ;
        }

        $qb
            ->getQuery()
            ->execute()
        ;
    }

    public function findByIdentifier(string $referralIdentifier): ?Referral
    {
        return $this->findOneBy(['identifier' => $referralIdentifier]);
    }

    public function getStatistics(Adherent $referrer): array
    {
        return $this->createQueryBuilder('referral')
            ->select('COUNT(IF(referral.status = :status_finished, referral.id, null)) AS nb_referral_finished')
            ->addSelect('COUNT(DISTINCT referral.id) AS nb_referral_sent')
            ->addSelect('COUNT(IF(referral.status = :status_reported, referral.id, null)) AS nb_referral_reported')
            ->where('referral.referrer = :referrer')
            ->setParameters([
                'status_finished' => StatusEnum::ADHESION_FINISHED->value,
                'status_reported' => StatusEnum::REPORTED->value,
                'referrer' => $referrer,
            ])
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function findFinishedForAdherent(Adherent $adherent): ?Referral
    {
        return $this->createQueryBuilder('referral')
            ->where('referral.status = :status_finished')
            ->andWhere('referral.referred = :referred')
            ->setParameters([
                'status_finished' => StatusEnum::ADHESION_FINISHED,
                'referred' => $adherent,
            ])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    public function countForReferrer(Adherent $adherent, array $statuses = [], array $types = []): int
    {
        $qb = $this->createQueryBuilder('referral')
            ->select('COUNT(DISTINCT referral.id)')
            ->where('referral.referrer = :referrer')
            ->setParameter('referrer', $adherent)
        ;

        if (!empty($statuses)) {
            $qb
                ->andWhere('referral.status IN (:statuses)')
                ->setParameter('statuses', $statuses)
            ;
        }

        if (!empty($types)) {
            $qb
                ->andWhere('referral.type IN (:types)')
                ->setParameter('types', $types)
            ;
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
