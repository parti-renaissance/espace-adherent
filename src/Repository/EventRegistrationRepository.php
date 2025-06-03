<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Collection\EventRegistrationCollection;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Entity\Event\RegistrationStatusEnum;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EventRegistrationRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRegistration::class);
    }

    public function findOneByUuid(string $uuid): ?EventRegistration
    {
        self::validUuid($uuid);

        return $this
            ->createQueryBuilder('r')
            ->select('r, e')
            ->leftJoin('r.event', 'e')
            ->where('r.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return EventRegistration[]
     */
    public function findByEventAndUuid(Event $event, array $uuids): array
    {
        self::validUuids($uuids);

        return $this->findBy(['event' => $event, 'uuid' => $uuids]);
    }

    public function findAdherentRegistration(string $eventUuid, string $adherentUuid, ?RegistrationStatusEnum $status = RegistrationStatusEnum::CONFIRMED): ?EventRegistration
    {
        self::validUuid($adherentUuid);

        $qb = $this->createEventRegistrationQueryBuilder($eventUuid)
            ->innerJoin('r.adherent', 'a')
            ->andWhere('a.uuid = :adherent_uuid')
            ->setParameter('adherent_uuid', $adherentUuid)
        ;

        if ($status) {
            $qb
                ->andWhere('r.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findGuestRegistration(string $eventUuid, string $emailAddress): ?EventRegistration
    {
        return $this->createEventRegistrationQueryBuilder($eventUuid)
            ->andWhere('r.emailAddress = :email_address')
            ->setParameter('email_address', $emailAddress)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return EventRegistration[]
     */
    public function findUpcomingAdherentRegistrations(string $adherentUuid): array
    {
        self::validUuid($adherentUuid);

        $registrations = $this->createAdherentEventRegistrationQueryBuilder($adherentUuid)
            ->andWhere('e.published = true')
            ->andWhere('e.beginAt >= :begin')
            // The extra 24 hours enable to include events in foreign
            // countries that are on different timezones.
            ->setParameter('begin', new Chronos('-24 hours'))
            ->orderBy('e.beginAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this->createEventRegistrationCollection($registrations)
            ->getUpcomingRegistrations()
            ->toArray()
        ;
    }

    public function findActivityUpcomingAdherentRegistrations(
        Adherent $adherent,
        int $page = 1,
        int $limit = 5,
    ): PaginatorInterface {
        $queryBuilder = $this->createAdherentEventRegistrationQueryBuilder($adherent->getUuidAsString())
            ->andWhere('e.published = true')
            ->andWhere('e.beginAt >= CONVERT_TZ(:now, \'Europe/Paris\', e.timeZone)')
            ->setParameter('now', new Chronos('now'))
            ->orderBy('e.beginAt', 'ASC')
        ;

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    /**
     * @return EventRegistration[]
     */
    public function findPastAdherentRegistrations(string $adherentUuid): array
    {
        self::validUuid($adherentUuid);

        $registrations = $this
            ->createAdherentEventRegistrationQueryBuilder($adherentUuid)
            ->andWhere('e.published = true')
            ->andWhere('e.finishAt < :finish')
            // The extra 24 hours enable to include events in foreign
            // countries that are on different timezones.
            ->setParameter('finish', new Chronos('+24 hours'))
            ->orderBy('e.finishAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $this
            ->createEventRegistrationCollection($registrations)
            ->getPastRegistrations()
            ->toArray()
        ;
    }

    public function findActivityPastAdherentRegistrations(
        Adherent $adherent,
        int $page = 1,
        int $limit = 5,
    ): PaginatorInterface {
        $queryBuilder = $this
            ->createAdherentEventRegistrationQueryBuilder($adherent->getUuidAsString())
            ->andWhere('e.published = true')
            ->andWhere('e.finishAt < CONVERT_TZ(:now, \'Europe/Paris\', e.timeZone)')
            ->setParameter('now', new Chronos('now'))
            ->orderBy('e.finishAt', 'DESC')
        ;

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    public function anonymizeAdherentRegistrations(Adherent $adherent): void
    {
        $qb = $this->createQueryBuilder('r');

        $qb->update()
            ->set('r.adherent', 'NULL')
            ->set('r.firstName', $qb->expr()->literal('Anonyme'))
            ->set('r.emailAddress', 'NULL')
            ->where('r.adherent = :adherent')
            ->setParameter(':adherent', $adherent)
            ->getQuery()
            ->execute()
        ;
    }

    public function findByEvent(Event $event): EventRegistrationCollection
    {
        $registrations = $this->createQueryBuilder('er')
            ->where('er.event = :event')
            ->andWhere('er.emailAddress IS NOT NULL')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;

        return $this->createEventRegistrationCollection($registrations);
    }

    /**
     * @return EventRegistration[]|PaginatorInterface|iterable
     */
    public function findPaginatedByEvent(Event $event, int $page = 1, ?int $limit = 30): iterable
    {
        return $this->configurePaginator(
            $this->createQueryBuilderByEvent($event),
            $page,
            $limit,
            null,
            false
        );
    }

    public function iterateByEvent(Event $event): \Iterator
    {
        return $this->createQueryBuilderByEvent($event)->getQuery()->iterate();
    }

    private function createQueryBuilderByEvent(Event $event): QueryBuilder
    {
        return $this
            ->createQueryBuilder('er')
            ->addSelect('a')
            ->leftJoin('er.adherent', 'a')
            ->where('er.event = :event')
            ->andWhere('er.emailAddress IS NOT NULL')
            ->orderBy('er.createdAt', 'ASC')
            ->setParameters(['event' => $event])
        ;
    }

    public function findAdherentMembersOfEvent(Event $event): array
    {
        $registrations = $this->createQueryBuilder('er')
            ->select('er', 'a')
            ->innerJoin('er.adherent', 'a')
            ->where('er.event = :event')
            ->andWhere('a.status = :status')
            ->setParameter('event', $event)
            ->setParameter('status', Adherent::ENABLED)
            ->getQuery()
            ->getResult()
        ;

        return array_map(
            static fn (EventRegistration $eventRegistration): Adherent => $eventRegistration->getAdherent(),
            $registrations
        );
    }

    public function isAlreadyRegistered(string $email, Event $event): bool
    {
        return (bool) $this
            ->createQueryBuilder('er')
            ->select('COUNT(1)')
            ->where('er.emailAddress = :email')
            ->andWhere('er.event = :event')
            ->setParameter('email', $email)
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countEventParticipantsWithoutCreator(Event $event): int
    {
        $qb = $this->createQueryBuilder('er')
            ->select('COUNT(DISTINCT er.id)')
            ->leftJoin('er.adherent', 'a')
            ->where('er.event = :event')
            ->andWhere('er.emailAddress IS NOT NULL')
            ->setParameter('event', $event)
        ;

        if ($author = $event->getAuthor()) {
            $qb->andWhere('a IS NULL OR a != :author')->setParameter('author', $author);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    private function createEventRegistrationQueryBuilder(string $eventUuid): QueryBuilder
    {
        self::validUuid($eventUuid);

        return $this->createQueryBuilder('r')
            ->select('r, e')
            ->leftJoin('r.event', 'e')
            ->where('e.uuid = :event_uuid')
            ->setParameter('event_uuid', $eventUuid)
        ;
    }

    private function createAdherentEventRegistrationQueryBuilder(string $adherentUuid): QueryBuilder
    {
        return $this->createQueryBuilder('er')
            ->select('er', 'e')
            ->leftJoin('er.event', 'e')
            ->innerJoin('er.adherent', 'adherent')
            ->where('adherent.uuid = :adherent')
            ->setParameter('adherent', $adherentUuid)
        ;
    }

    private function createEventRegistrationCollection(array $registrations): EventRegistrationCollection
    {
        return new EventRegistrationCollection($registrations);
    }

    public function findInvitationByEventAndAdherent(Event $event, Adherent $adherent): ?EventRegistration
    {
        return $this->createQueryBuilder('er')
            ->select('er')
            ->where('er.event = :event')
            ->andWhere('er.adherent = :adherent')
            ->andWhere('er.status = :status')
            ->setParameter('event', $event)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
