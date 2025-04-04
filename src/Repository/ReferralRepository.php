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
            ->where('referral.emailAddress = :email')
            ->andWhere('referral.status = :status_reported')
            ->setParameters([
                'email' => $email,
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
            ->setParameters([
                'new_status' => StatusEnum::ADHESION_FINISHED,
                'date' => new \DateTimeImmutable(),
                'status_to_update' => [StatusEnum::ACCOUNT_CREATED, StatusEnum::INVITATION_SENT],
            ])
            ->andWhere('r.referred = :referred')
            ->setParameter('referred', $adherent)
            ->getQuery()
            ->execute()
        ;
    }

    public function updateReferralsStatus(Adherent $adherent, ?Referral $referral, StatusEnum $status): void
    {
        $qb = $this->createQueryBuilder('r')
            ->update()
            ->set('r.status', ':new_status')
            ->set('r.updatedAt', ':date')
            ->andWhere('r.status = :status_to_update')
        ;

        $condition = $qb->expr()->orX($qb->expr()->andX(
            $qb->expr()->eq('r.firstName', ':firstName'),
            $qb->expr()->eq('r.lastName', ':lastName'),
            $qb->expr()->eq('r.emailAddress', ':email')
        ));

        $qb->setParameters([
            'firstName' => $adherent->getFirstName(),
            'lastName' => $adherent->getLastName(),
            'email' => $adherent->getEmailAddress(),
            'new_status' => $status,
            'date' => new \DateTimeImmutable(),
            'status_to_update' => StatusEnum::INVITATION_SENT,
        ]);

        if ($referral) {
            $condition->add('r.referred = :referred');
            $qb
                ->andWhere('r.id != :id')
                ->setParameter('id', $referral->getId())
                ->setParameter('referred', $adherent)
            ;
        }

        $qb
            ->andWhere($condition)
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
}
