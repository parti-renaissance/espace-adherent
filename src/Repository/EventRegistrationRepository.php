<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Adherent\Tag\TagEnum;
use App\Collection\EventRegistrationCollection;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use App\Entity\PushToken;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function getByEventAndUuid(BaseEvent $event, array $uuids): EventRegistrationCollection
    {
        self::validUuids($uuids);

        return $this->createEventRegistrationCollection($this->findBy(['event' => $event, 'uuid' => $uuids]));
    }

    public function findAdherentRegistration(string $eventUuid, string $adherentUuid): ?EventRegistration
    {
        self::validUuid($adherentUuid);

        return $this->createEventRegistrationQueryBuilder($eventUuid)
            ->andWhere('r.adherentUuid = :adherent_uuid')
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
        int $limit = 5
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
        int $limit = 5
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
            ->set('r.adherentUuid', $qb->expr()->literal('null'))
            ->set('r.firstName', $qb->expr()->literal('Anonyme'))
            ->set('r.emailAddress', $qb->expr()->literal('null'))
            ->where('r.adherentUuid = :uuid')
            ->setParameter(':uuid', $adherent->getUuid()->toString())
            ->getQuery()
            ->execute()
        ;
    }

    public function findByEvent(BaseEvent $event): EventRegistrationCollection
    {
        return $this->createEventRegistrationCollection($this->findBy(['event' => $event]));
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
            ->createQueryBuilder('event_registration')
            ->select('event_registration.createdAt AS subscription_date')
            ->addSelect('IF(adherent.id IS NOT NULL, IF(adherent.tags LIKE :adherent_tag, \'adherent\', \'sympathisant\'), \'contact\') AS type')
            ->addSelect('IF(adherent.id IS NOT NULL, adherent.firstName, event_registration.firstName) AS first_name')
            ->addSelect('IF(adherent.id IS NOT NULL, adherent.lastName, event_registration.lastName) AS last_name')
            ->addSelect('IF(adherent.id IS NOT NULL, adherent.postAddress.postalCode, event_registration.postalCode) AS postal_code')
            ->addSelect('IF(adherent.id IS NOT NULL, adherent.emailAddress, event_registration.emailAddress) AS email_address')
            ->addSelect('adherent.phone')
            ->addSelect('adherent.tags')
            ->leftJoin(
                Adherent::class,
                'adherent',
                Join::WITH,
                'event_registration.adherentUuid = adherent.uuid'
            )
            ->where('event_registration.event = :event')
            ->orderBy('event_registration.createdAt', 'ASC')
            ->setParameters([
                'event' => $event,
                'adherent_tag' => '%'.TagEnum::ADHERENT.':%',
            ])
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

    public function findPushTokenIdentifiers(BaseEvent $event): array
    {
        $tokens = $this->createEventRegistrationQueryBuilder($event->getUuidAsString())
            ->select('DISTINCT(token.identifier)')
            ->innerJoin(Adherent::class, 'adherent', Join::WITH, 'r.adherentUuid = adherent.uuid')
            ->innerJoin(PushToken::class, 'token', Join::WITH, 'token.adherent = adherent')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map('current', $tokens);
    }

    public function countEventParticipantsWithoutCreator(BaseEvent $event): int
    {
        return (int) $this->createQueryBuilder('event_registration')
            ->select('COUNT(1)')
            ->where('event_registration.event = :event AND event_registration.adherentUuid != :organiser_uuid')
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
            ->where('er.adherentUuid = :adherent')
            ->setParameter('adherent', $adherentUuid)
        ;
    }

    private function createEventRegistrationCollection(array $registrations): EventRegistrationCollection
    {
        return new EventRegistrationCollection($registrations);
    }
}
