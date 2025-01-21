<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Collection\EventRegistrationCollection;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
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
    public function findByEventAndUuid(BaseEvent $event, array $uuids): array
    {
        self::validUuids($uuids);

        return $this->findBy(['event' => $event, 'uuid' => $uuids]);
    }

    public function findAdherentRegistration(string $eventUuid, string $adherentUuid): ?EventRegistration
    {
        self::validUuid($adherentUuid);

        return $this->createEventRegistrationQueryBuilder($eventUuid)
            ->innerJoin('r.adherent', 'a')
            ->andWhere('a.uuid = :adherent_uuid')
            ->setParameter('adherent_uuid', $adherentUuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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

    public function findByEvent(BaseEvent $event): EventRegistrationCollection
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
    public function findPaginatedByEvent(BaseEvent $event, int $page = 1, ?int $limit = 30): iterable
    {
        return $this->configurePaginator(
            $this->createQueryBuilderByEvent($event),
            $page,
            $limit,
            null,
            false
        );
    }

    public function iterateByEvent(BaseEvent $event): \Iterator
    {
        return $this->createQueryBuilderByEvent($event)->getQuery()->iterate();
    }

    private function createQueryBuilderByEvent(BaseEvent $event): QueryBuilder
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

    public function isAlreadyRegistered(string $email, BaseEvent $event): bool
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

    public function countEventParticipantsWithoutCreator(BaseEvent $event): int
    {
        return (int) $this->createQueryBuilder('event_registration')
            ->select('COUNT(1)')
            ->leftJoin('event_registration.adherent', 'adherent')
            ->where('event_registration.event = :event AND (adherent.uuid IS NULL OR adherent.uuid != :organiser_uuid)')
            ->andWhere('event_registration.emailAddress IS NOT NULL')
            ->setParameter('event', $event)
            ->setParameter('organiser_uuid', $event->getOrganizer()->getUuid()->toString())
            ->getQuery()
            ->getSingleScalarResult()
        ;
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
}
