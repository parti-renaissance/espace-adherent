<?php

namespace App\Repository;

use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneTagEnum;
use App\Entity\Referral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;

class ReferralRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

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
        [$joins, $filter, $parameters, $types] = $this->buildZoneJoinsAndFilter($zone ? [$zone] : [], true);

        $sql = <<<SQL
                SELECT
                    COUNT(DISTINCT referral.id) AS referrals_count,
                    adherent.first_name AS first_name,
                    CONCAT(UPPER(SUBSTRING(adherent.last_name, 1, 1)), '.') AS last_name,
                    CONCAT(zone_assembly.name, ' (', zone_assembly.code, ')') AS assembly,
                    RANK() OVER (ORDER BY COUNT(DISTINCT referral.id) DESC) AS position
                FROM referral
                INNER JOIN adherents AS adherent
                    ON referral.referrer_id = adherent.id
                {$joins}
                WHERE referral.status = :status
                {$filter}
                GROUP BY adherent.id, adherent.first_name, adherent.last_name, zone_assembly.name, zone_assembly.code
                ORDER BY referrals_count DESC
                LIMIT {$limit}
            SQL;

        $parameters['status'] = StatusEnum::ADHESION_FINISHED->value;

        return $this->getEntityManager()->getConnection()->executeQuery($sql, $parameters, $types)->fetchAllAssociative();
    }

    public function getReferrerRank(int $referrerId, ?Zone $zone = null): ?int
    {
        [$joins, $filter, $parameters, $types] = $this->buildZoneJoinsAndFilter($zone ? [$zone] : []);

        $sql = <<<SQL
                SELECT position FROM (
                    SELECT
                        adherent.id AS referrer_id,
                        RANK() OVER (ORDER BY COUNT(DISTINCT referral.id) DESC) AS position
                    FROM referral
                    INNER JOIN adherents AS adherent
                        ON referral.referrer_id = adherent.id
                    {$joins}
                    WHERE referral.status = :status
                    {$filter}
                    GROUP BY adherent.id
                ) AS ranked
                WHERE referrer_id = :referrer_id
            SQL;

        $parameters['referrer_id'] = $referrerId;
        $parameters['status'] = StatusEnum::ADHESION_FINISHED->value;

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql, $parameters, $types)->fetchOne();

        return false !== $result ? (int) $result : null;
    }

    public function getManagerScoreboard(array $zones): array
    {
        if (empty($zones)) {
            return [];
        }

        [$joins, $filter, $parameters, $types] = $this->buildZoneJoinsAndFilter($zones);

        $sql = <<<SQL
                SELECT
                    adherent.public_id AS pid,
                    adherent.uuid,
                    adherent.first_name,
                    adherent.last_name,
                    adherent.gender,
                    adherent.image_name AS profile_image,
                    COUNT(DISTINCT CASE WHEN referral.status = :status_finished THEN referral.id END) AS count_adhesion_finished,
                    COUNT(DISTINCT CASE WHEN referral.type IN (:invitation_types) THEN referral.id END) AS count_invitations,
                    COUNT(DISTINCT CASE WHEN referral.status = :status_reported THEN referral.id END) AS count_reported
                FROM referral
                INNER JOIN adherents AS adherent ON referral.referrer_id = adherent.id
                {$joins}
                WHERE adherent.id IS NOT NULL
                {$filter}
                GROUP BY adherent.id
                ORDER BY count_adhesion_finished DESC
            SQL;

        $parameters += [
            'status_finished' => StatusEnum::ADHESION_FINISHED->value,
            'status_account_created' => StatusEnum::ACCOUNT_CREATED->value,
            'status_reported' => StatusEnum::REPORTED->value,
            'invitation_types' => [TypeEnum::INVITATION->value, TypeEnum::PREREGISTRATION->value],
        ];

        $types['invitation_types'] = ArrayParameterType::STRING;

        return $this->getEntityManager()->getConnection()->executeQuery($sql, $parameters, $types)->fetchAllAssociative();
    }

    private function buildZoneJoinsAndFilter(array $zones, bool $withAssembly = false): array
    {
        $parameters = [];

        $joins = <<<SQL
                INNER JOIN adherent_zone AS adherent_zone ON adherent_zone.adherent_id = adherent.id
                INNER JOIN geo_zone_parent AS zone_parent ON zone_parent.child_id = adherent_zone.zone_id
            SQL;

        if ($withAssembly) {
            $joins .= <<<SQL
                    INNER JOIN geo_zone AS zone_assembly ON zone_parent.parent_id = zone_assembly.id AND zone_assembly.tags LIKE :zone_tag
                SQL;
            $parameters['zone_tag'] = \sprintf('%%%s%%', ZoneTagEnum::ASSEMBLY);
        }

        if (!empty($zones)) {
            $filter = <<<SQL
                    AND (adherent_zone.zone_id IN (:zone_ids) OR zone_parent.parent_id IN (:zone_ids))
                SQL;
            $parameters['zone_ids'] = array_map(static fn (Zone $zone) => $zone->getId(), $zones);
            $types = ['zone_ids' => ArrayParameterType::INTEGER];
        }

        return [$joins, $filter ?? '', $parameters, $types ?? []];
    }
}
