<?php

namespace App\Repository;

use App\Adherent\Referral\StatusEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
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

    public function getScoreboard(?Zone $zone = null, int $limit = 10): array
    {
        [$zoneJoins, $zoneFilter, $parameters] = $this->buildZoneSqlParts($zone);

        $sql = <<<SQL
                SELECT
                    COUNT(DISTINCT referral.id) AS nb_referral,
                    referrer.first_name AS firstName,
                    CONCAT(UPPER(SUBSTRING(referrer.last_name, 1, 1)), '.') AS lastNameInitial,
                    RANK() OVER (ORDER BY COUNT(DISTINCT referral.id) DESC) AS position
                FROM referral AS referral
                INNER JOIN adherents AS referrer
                    ON referral.referrer_id = referrer.id
                {$zoneJoins}
                WHERE referral.status = :status
                {$zoneFilter}
                GROUP BY referrer.id, referrer.first_name, referrer.last_name
                ORDER BY nb_referral DESC
                LIMIT {$limit}
            SQL;

        $parameters = array_merge($parameters, [
            'status' => StatusEnum::ADHESION_FINISHED->value,
        ]);
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $result = $stmt->executeQuery($parameters);


        return $result->fetchAllAssociative();
    }

    public function getReferrerRank(Adherent $referrer, ?Zone $zone = null): ?int
    {
        [$zoneJoins, $zoneFilter, $parameters] = $this->buildZoneSqlParts($zone);

        $sql = <<<SQL
                SELECT position FROM (
                    SELECT
                        referrer.id AS referrer_id,
                        RANK() OVER (ORDER BY COUNT(DISTINCT referral.id) DESC) AS position
                    FROM referral AS referral
                    INNER JOIN adherents AS referrer
                        ON referral.referrer_id = referrer.id
                    {$zoneJoins}
                    WHERE referral.status = :status
                    {$zoneFilter}
                    GROUP BY referrer.id
                ) AS ranked
                WHERE referrer_id = :referrer_id
            SQL;

        $parameters = array_merge($parameters, [
            'referrer_id' => $referrer->getId(),
            'status' => StatusEnum::ADHESION_FINISHED->value,
        ]);
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $result = $stmt->executeQuery($parameters)->fetchOne();

        return false !== $result ? (int) $result : null;
    }

    private function buildZoneSqlParts(?Zone $zone): array
    {
        $joins = '';
        $filter = '';
        $parameters = [];

        if ($zone) {
            $joins = <<<SQL
                    INNER JOIN adherent_zone AS adherent_zone
                        ON adherent_zone.adherent_id = referrer.id
                    INNER JOIN geo_zone_parent AS adherent_zone_parent
                        ON adherent_zone_parent.child_id = adherent_zone.zone_id
                SQL;

            $filter = <<<SQL
                    AND (
                        adherent_zone.zone_id = :zone_id
                        OR adherent_zone_parent.parent_id = :zone_id
                    )
                SQL;

            $parameters['zone_id'] = $zone->getId();
        }

        return [$joins, $filter, $parameters];
    }
}
