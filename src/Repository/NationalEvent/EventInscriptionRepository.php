<?php

declare(strict_types=1);

namespace App\Repository\NationalEvent;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Adherent\Tag\TagGenerator\EventTagGenerator;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\InscriptionReminder;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\PushToken;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\NationalEventTypeEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\PublicId\PublicIdRepositoryInterface;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use App\Repository\UpdateAdherentLinkRepositoryInterface;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class EventInscriptionRepository extends ServiceEntityRepository implements PublicIdRepositoryInterface, UpdateAdherentLinkRepositoryInterface
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventInscription::class);
    }

    /**
     * @return EventInscription[]
     */
    public function findAllForTags(Adherent $adherent): array
    {
        return $this->createQueryBuilder('ei')
            ->select('PARTIAL ei.{id, uuid, status, createdAt, firstTicketScannedAt}')
            ->addSelect('e')
            ->innerJoin('ei.event', 'e')
            ->where('ei.adherent = :adherent')
            ->andWhere('e.startDate >= :start_date')
            ->andWhere('e.type IN (:types)')
            ->andWhere('ei.status NOT IN (:excluded_statuses)')
            ->andWhere('((e.endDate <= :now AND ei.firstTicketScannedAt IS NOT NULL) OR e.endDate > :now)')
            ->setParameters([
                'now' => new \DateTime(),
                'adherent' => $adherent,
                'start_date' => new \DateTime(EventTagGenerator::PERIOD),
                'excluded_statuses' => [
                    InscriptionStatusEnum::CANCELED,
                    InscriptionStatusEnum::DUPLICATE,
                    InscriptionStatusEnum::REFUSED,
                ],
                'types' => [
                    NationalEventTypeEnum::DEFAULT,
                    NationalEventTypeEnum::CAMPUS,
                    NationalEventTypeEnum::NRP,
                ],
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return EventInscription[]
     */
    public function findAllForAdherentAndEvent(Adherent $adherent, NationalEvent $event): array
    {
        return $this->createQueryBuilder('ei')
            ->addSelect('CASE WHEN ei.status = :status_accepted THEN 1
                WHEN ei.status = :status_inconclusive THEN 2
                ELSE 3 END AS HIDDEN score')
            ->where('ei.adherent = :adherent')
            ->andWhere('ei.event = :event')
            ->andWhere('ei.status NOT IN (:excluded_statuses)')
            ->setParameters([
                'adherent' => $adherent,
                'event' => $event,
                'excluded_statuses' => [InscriptionStatusEnum::CANCELED, InscriptionStatusEnum::DUPLICATE],
                'status_accepted' => InscriptionStatusEnum::ACCEPTED,
                'status_inconclusive' => InscriptionStatusEnum::INCONCLUSIVE,
            ])
            ->orderBy('score', 'ASC')
            ->addOrderBy('ei.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return EventInscription[]
     */
    public function findAcceptedByEmail(string $email, ?NationalEvent $excludedEvent = null): array
    {
        $qb = $this->createQueryBuilder('ei')
            ->where('ei.addressEmail = :email')
            ->andWhere('ei.status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', InscriptionStatusEnum::ACCEPTED)
        ;

        if ($excludedEvent) {
            $qb
                ->andWhere('ei.event != :excluded_event')
                ->setParameter('excluded_event', $excludedEvent)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllForEventPaginated(NationalEvent $event, ?string $searchTerm, array $statuses, int $page = 1, $limit = 30): PaginatorInterface
    {
        $queryBuilder = $this->createQueryBuilder('ei')
            ->addSelect('adherent')
            ->leftJoin('ei.adherent', 'adherent')
            ->where('ei.event = :event')
            ->andWhere('ei.status IN (:statuses)')
            ->setParameter('event', $event)
            ->setParameter('statuses', $statuses)
            ->orderBy('ei.createdAt', 'DESC')
        ;

        if ($searchTerm) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('ei.firstName', ':searchTerm'),
                    $queryBuilder->expr()->like('ei.lastName', ':searchTerm'),
                    $queryBuilder->expr()->like('ei.addressEmail', ':searchTerm'),
                    $queryBuilder->expr()->like('ei.uuid', ':searchTerm'),
                ))
                ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ;
        }

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    /**
     * @return EventInscription[]
     */
    public function findAllPartialForEvent(NationalEvent $event, ?bool $emailAlreadySent = null, ?bool $pushAlreadySent = null): array
    {
        $qb = $this->createQueryBuilder('ei')
            ->select('PARTIAL ei.{id, uuid}')
             ->where('ei.event = :event')
             ->andWhere('ei.status IN (:statuses)')
             ->setParameter('event', $event)
             ->setParameter('statuses', InscriptionStatusEnum::APPROVED_STATUSES)
        ;

        if (null !== $emailAlreadySent) {
            $qb->andWhere('ei.ticketSentAt IS '.(true === $emailAlreadySent ? 'NOT' : '').' NULL');
        }

        if (null !== $pushAlreadySent) {
            $qb
                ->innerJoin('ei.adherent', 'adherent')
                ->innerJoin(PushToken::class, 'token', Join::WITH, 'token.adherent = adherent')
                ->andWhere('ei.pushSentAt IS '.(true === $pushAlreadySent ? 'NOT' : '').' NULL')
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function countWithoutTicketQRCodes(NationalEvent $event): int
    {
        return $this->createQueryBuilder('ei')
            ->select('COUNT(ei)')
            ->where('ei.event = :event')
            ->andWhere('ei.ticketQRCodeFile IS NULL')
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countTickets(NationalEvent $event, bool $withoutTicket, array $statuses): int
    {
        return $this->createQueryBuilder('ei')
            ->select('COUNT(DISTINCT ei.id)')
            ->where('ei.event = :event')
            ->andWhere('ei.ticketSentAt IS '.($withoutTicket ? '' : 'NOT').' NULL')
            ->andWhere('ei.status IN (:statuses)')
            ->setParameter('event', $event)
            ->setParameter('statuses', $statuses)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countForPush(NationalEvent $event, bool $firstNotification = false): int
    {
        $queryBuilder = $this->createQueryBuilder('ei')
            ->select('COUNT(DISTINCT adherent.id)')
            ->innerJoin('ei.adherent', 'adherent')
            ->innerJoin(PushToken::class, 'token', Join::WITH, 'token.adherent = adherent')
            ->where('ei.event = :event')
            ->andWhere('ei.status IN (:statuses)')
            ->setParameter('event', $event)
            ->setParameter('statuses', InscriptionStatusEnum::APPROVED_STATUSES)
        ;

        if ($firstNotification) {
            $queryBuilder->andWhere('ei.pushSentAt IS NULL');
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return EventInscription[]
     */
    public function findAllWithoutTickets(NationalEvent $event): array
    {
        return $this->createQueryBuilder('ei')
            ->select('PARTIAL ei.{id, uuid}')
            ->where('ei.event = :event')
            ->andWhere('ei.ticketQRCodeFile IS NULL')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByStatus(NationalEvent $event): array
    {
        return array_column($this->createQueryBuilder('ei', 'ei.status')
            ->select('ei.status, COUNT(ei) as count')
            ->where('ei.event = :event')
            ->groupBy('ei.status')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult(),
            'count',
            'status');
    }

    public function findDuplicate(EventInscription $eventInscription): ?EventInscription
    {
        return $this->createQueryBuilder('ei')
            ->addSelect('
                CASE
                    WHEN ei.status = :status_accepted THEN 1
                    WHEN ei.status = :status_inconclusive THEN 2
                    WHEN ei.status = :status_refused THEN 3
                    WHEN ei.status = :status_waiting_payment THEN 5
                    WHEN ei.status = :status_pending THEN 6
                    ELSE 7
                END AS HIDDEN priority
            ')
            ->where('ei.id != :event_inscription_id')
            ->andWhere('ei.event = :event')
            ->andWhere('ei.addressEmail = :email')
            ->andWhere('ei.firstName = :first_name')
            ->andWhere('ei.lastName = :last_name')
            ->andWhere('ei.status != :status')
            ->orderBy('priority', 'ASC')
            ->addOrderBy('ei.createdAt', 'ASC')
            ->setParameters([
                'event_inscription_id' => $eventInscription->getId(),
                'event' => $eventInscription->event,
                'email' => $eventInscription->addressEmail,
                'first_name' => $eventInscription->firstName,
                'last_name' => $eventInscription->lastName,
                'status' => InscriptionStatusEnum::DUPLICATE,
                'status_accepted' => InscriptionStatusEnum::ACCEPTED,
                'status_inconclusive' => InscriptionStatusEnum::INCONCLUSIVE,
                'status_refused' => InscriptionStatusEnum::REFUSED,
                'status_waiting_payment' => InscriptionStatusEnum::WAITING_PAYMENT,
                'status_pending' => InscriptionStatusEnum::PENDING,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function countPackageValues(int $eventId, array $targetKeys = []): array
    {
        $sql = <<<SQL
                SELECT
                    deduplicated.json_key as option_key,
                    deduplicated.json_value as option_value,
                    COUNT(*) as total_count
                FROM (
                    SELECT DISTINCT
                        combined.inscription_id,
                        combined.json_key,
                        combined.json_value
                    FROM (
                        SELECT
                            ei.id as inscription_id,
                            jt.dynamic_key as json_key,
                            JSON_UNQUOTE(JSON_EXTRACT(ei.package_values, CONCAT('$.', jt.dynamic_key))) as json_value
                        FROM national_event_inscription ei,
                        JSON_TABLE(JSON_KEYS(ei.package_values), "$[*]" COLUMNS (dynamic_key VARCHAR(255) PATH "$")) as jt
                        WHERE ei.event_id = :eventId
                          AND ei.status IN (:inscriptionStatuses)

                        UNION ALL

                        SELECT
                            ei.id as inscription_id,
                            jt.dynamic_key as json_key,
                            JSON_UNQUOTE(JSON_EXTRACT(p.package_values, CONCAT('$.', jt.dynamic_key))) as json_value
                        FROM national_event_inscription_payment p
                        INNER JOIN national_event_inscription ei ON p.inscription_id = ei.id
                        , JSON_TABLE(JSON_KEYS(p.package_values), "$[*]" COLUMNS (dynamic_key VARCHAR(255) PATH "$")) as jt
                        WHERE p.status = :paymentStatus
                          AND ei.event_id = :eventId
                          AND ei.status IN (:inscriptionStatuses)
                    ) as combined
                ) as deduplicated
                WHERE (:filterKeys = 0 OR deduplicated.json_key IN (:targetKeys))
                GROUP BY deduplicated.json_key, deduplicated.json_value
                ORDER BY deduplicated.json_key, deduplicated.json_value
            SQL;

        $allStatuses = array_merge(InscriptionStatusEnum::APPROVED_STATUSES, [
            InscriptionStatusEnum::WAITING_PAYMENT,
            InscriptionStatusEnum::PENDING,
            InscriptionStatusEnum::INCONCLUSIVE,
        ]);

        $params = [
            'eventId' => $eventId,
            'inscriptionStatuses' => $allStatuses,
            'paymentStatus' => PaymentStatusEnum::PENDING->value,
            'targetKeys' => $targetKeys,
            'filterKeys' => \count($targetKeys) > 0 ? 1 : 0,
        ];

        $types = [
            'eventId' => \PDO::PARAM_INT,
            'inscriptionStatuses' => ArrayParameterType::STRING,
            'paymentStatus' => \PDO::PARAM_STR,
            'targetKeys' => ArrayParameterType::STRING,
            'filterKeys' => \PDO::PARAM_INT,
        ];

        $connection = $this->getEntityManager()->getConnection();
        $results = $connection->executeQuery($sql, $params, $types)->fetchAllAssociative();

        $formatted = [];
        foreach ($results as $row) {
            $key = $row['option_key'];
            $value = $row['option_value'];
            $count = $row['total_count'];

            if (!isset($formatted[$key])) {
                $formatted[$key] = [];
            }
            $formatted[$key][$value] = $count;
        }

        return $formatted;
    }

    public function findAllWithPendingPayments(\DateTime $now): array
    {
        return $this->createQueryBuilder('ei')
            ->select('PARTIAL ei.{id, uuid}')
            ->leftJoin(InscriptionReminder::class, 'r', Join::WITH, "r.inscription = ei AND r.type = (
                CASE WHEN ROUND(TIMESTAMPDIFF(MINUTE, ei.createdAt, :now)) < 60 THEN 'payment_10'
                WHEN ROUND(TIMESTAMPDIFF(MINUTE, ei.createdAt, :now)) < 360 THEN 'payment_60'
                WHEN ROUND(TIMESTAMPDIFF(MINUTE, ei.createdAt, :now)) < 720 THEN 'payment_360'
                ELSE 'payment_1200' END
            )")
            ->where('ei.status NOT IN (:statuses)')
            ->andWhere('ei.paymentStatus != :payment_status')
            ->andWhere('r.id IS NULL')
            ->andWhere('ei.createdAt < :since')
            ->setParameter('now', $now)
            ->setParameter('since', (clone $now)->modify('-10 minutes'))
            ->setParameter('statuses', InscriptionStatusEnum::REJECTED_STATUSES)
            ->setParameter('payment_status', PaymentStatusEnum::CONFIRMED)
            ->getQuery()
            ->getResult()
        ;
    }

    public function cancelAllWithWaitingPayments(\DateTime $now): void
    {
        $cutoffDate = (clone $now)->modify(\sprintf('-%d minutes', EventInscription::CANCELLATION_DELAY_IN_MIN));

        $this->createQueryBuilder('ei')
            ->update()
            ->set('ei.status', ':new_status')
            ->set('ei.canceledAt', ':canceled_at')
            ->where('ei.status = :status')
            ->andWhere('ei.updatedAt < :cutoff')
            ->setParameter('new_status', InscriptionStatusEnum::CANCELED)
            ->setParameter('canceled_at', $now)
            ->setParameter('status', InscriptionStatusEnum::WAITING_PAYMENT)
            ->setParameter('cutoff', $cutoffDate)
            ->getQuery()
            ->execute();
    }

    public function closeWithWaitingPayment(EventInscription $eventInscription): void
    {
        $this->createQueryBuilder('ei')
            ->update()
            ->set('ei.status', ':new_status')
            ->set('ei.canceledAt', ':canceled_at')
            ->where('ei.status = :status')
            ->andWhere('ei.addressEmail = :email')
            ->andWhere('ei.event = :event')
            ->setParameter('new_status', InscriptionStatusEnum::CANCELED)
            ->setParameter('status', InscriptionStatusEnum::WAITING_PAYMENT)
            ->setParameter('canceled_at', new \DateTime())
            ->setParameter('email', $eventInscription->addressEmail)
            ->setParameter('event', $eventInscription->event)
            ->getQuery()
            ->execute()
        ;
    }

    public function publicIdExists(string $publicId): bool
    {
        return $this->count(['publicId' => $publicId]) > 0;
    }

    public function findByPublicId(string $publicId): ?EventInscription
    {
        return $this->findOneBy(['publicId' => $publicId]);
    }

    public function findNextToValidate(?int $eventId): ?EventInscription
    {
        $qb = $this->createQueryBuilder('ei')
            ->where('ei.status = :status')
            ->setParameter('status', InscriptionStatusEnum::PENDING)
            ->setMaxResults(1)
            ->orderBy('ei.createdAt', 'ASC')
        ;

        if ($eventId) {
            $qb->andWhere('ei.event = :event')->setParameter('event', $eventId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return EventInscription[]
     */
    public function findAllForCurrentCampus(array $zones, array $committeeUuids, ?bool $withAdherent, ?string $search): array
    {
        $eventId = $this->getEntityManager()->createQueryBuilder()
            ->select('e.id')
            ->addSelect(
                'CASE
                    WHEN e.startDate <= :now AND e.endDate >= :now THEN 0
                    WHEN e.startDate > :now THEN 1
                    ELSE 2
                END AS HIDDEN score'
            )
            ->from(NationalEvent::class, 'e')
            ->where('e.type = :type')
            ->orderBy('score', 'ASC')
            ->setMaxResults(1)
            ->setParameters(['type' => NationalEventTypeEnum::CAMPUS, 'now' => new \DateTime()])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (!$eventId) {
            return [];
        }

        $qb = $this->createQueryBuilder('ei')
            ->addSelect('e', 'a')
            ->innerJoin('ei.event', 'e')
            ->leftJoin('ei.adherent', 'a')
            ->where('e.id = :event_id')
            ->andWhere('ei.status NOT IN (:excluded_statuses)')
            ->setParameter('event_id', $eventId)
            ->setParameter('excluded_statuses', [InscriptionStatusEnum::DUPLICATE, InscriptionStatusEnum::REFUSED, InscriptionStatusEnum::CANCELED])
            ->orderBy('ei.createdAt', 'DESC')
        ;

        if ($committeeUuids) {
            $zones = array_merge($zones, $this->getEntityManager()->createQueryBuilder()
                ->select('z')
                ->from(Zone::class, 'z')
                ->innerJoin(Committee::class, 'c')
                ->innerJoin('c.zones', 'z2', Join::WITH, 'z2 = z')
                ->where('c.uuid IN (:committee_uuids)')
                ->setParameter('committee_uuids', $committeeUuids)
                ->getQuery()
                ->getResult()
            );
        }

        $this->withGeoZones(
            $zones,
            $qb,
            'ei',
            EventInscription::class,
            'ei2',
            'zones',
            'z2',
        );

        if (null !== $withAdherent) {
            $qb->andWhere($withAdherent ? 'a IS NOT NULL' : 'a IS NULL');
        }

        if ($search) {
            MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                $qb,
                $search,
                [
                    ['ei.firstName', 'ei.lastName'],
                    ['ei.lastName', 'ei.firstName'],
                    ['ei.addressEmail', 'ei.addressEmail'],
                ],
            );
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllForAdherent(Adherent $roommateAdherent, NationalEvent $event, array $forbiddenStatuses): array
    {
        return $this->createQueryBuilder('ei')
            ->where('ei.adherent = :adherent')
            ->andWhere('ei.event = :event')
            ->andWhere('ei.status NOT IN (:forbidden_statuses)')
            ->setParameter('adherent', $roommateAdherent)
            ->setParameter('event', $event)
            ->setParameter('forbidden_statuses', $forbiddenStatuses)
            ->orderBy('ei.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function updateLinksWithNewAdherent(Adherent $adherent): void
    {
        $this
            ->createQueryBuilder('ei')
            ->update()
            ->set('ei.adherent', ':adherent')
            ->where('ei.adherent IS NULL')
            ->andWhere('ei.addressEmail = :email')
            ->andWhere('ei.createdAt > :created_after')
            ->setParameters([
                'adherent' => $adherent,
                'email' => $adherent->getEmailAddress(),
                'created_after' => new \DateTime('-6 months'),
            ])
            ->getQuery()
            ->execute()
        ;
    }

    /** @param EventInscription $object */
    public function updateAdherentLink(object $object): void
    {
        if ($object->adherent) {
            return;
        }

        $object->adherent = $this->getEntityManager()->getRepository(Adherent::class)->findOneBy(['emailAddress' => $object->addressEmail]);
    }
}
