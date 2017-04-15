<?php

namespace AppBundle\Repository;

use AppBundle\Collection\EventRegistrationCollection;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;

class EventRegistrationRepository extends EntityRepository
{
    public function findOneByUuid(string $uuid): ?EventRegistration
    {
        $query = $this
            ->createQueryBuilder('r')
            ->select('r, e')
            ->leftJoin('r.event', 'e')
            ->where('r.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * @param Event $event
     * @param array $uuids
     *
     * @return EventRegistration[]
     */
    public function findByUuidAndEvent(Event $event, array $uuids): array
    {
        return array_filter($this->findBy(['event' => $event]), function (EventRegistration $reg) use ($uuids) {
            return in_array($reg->getUuid()->toString(), $uuids);
        });
    }

    public function findAdherentRegistration(string $eventUuid, string $adherentUuid): ?EventRegistration
    {
        $uuid = Uuid::fromString($adherentUuid);

        $query = $this
            ->createEventRegistrationQueryBuilder($eventUuid)
            ->andWhere('r.adherentUuid = :adherent_uuid')
            ->setParameter('adherent_uuid', $uuid->toString())
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findGuestRegistration(string $eventUuid, string $emailAddress): ?EventRegistration
    {
        $query = $this
            ->createEventRegistrationQueryBuilder($eventUuid)
            ->andWhere('r.emailAddress = :email_address')
            ->setParameter('email_address', $emailAddress)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findUpcomingAdherentRegistrations(string $adherentUuid): array
    {
        $registrations = $this
            ->createAdherentEventRegistrationQueryBuilder($adherentUuid)
            ->andWhere('e.beginAt >= :begin')
            // The extra 24 hours enable to include events in foreign
            // countries that are on different timezones.
            ->setParameter('begin', new \DateTime('-24 hours'))
            ->orderBy('e.beginAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this
            ->createEventRegistrationCollection($registrations)
            ->getUpcomingRegistrations()
            ->toArray()
        ;
    }

    public function findPastAdherentRegistrations(string $adherentUuid): array
    {
        $registrations = $this
            ->createAdherentEventRegistrationQueryBuilder($adherentUuid)
            ->andWhere('e.finishAt < :finish')
            // The extra 24 hours enable to include events in foreign
            // countries that are on different timezones.
            ->setParameter('finish', new \DateTime('+24 hours'))
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

    private function createEventRegistrationCollection(array $registrations): EventRegistrationCollection
    {
        return new EventRegistrationCollection($registrations);
    }

    private function createAdherentEventRegistrationQueryBuilder(string $adherentUuid): QueryBuilder
    {
        $adherentUuid = Uuid::fromString($adherentUuid);

        return $this
            ->createQueryBuilder('er')
            ->select('er', 'e')
            ->leftJoin('er.event', 'e')
            ->where('er.adherentUuid = :adherent')
            ->setParameter('adherent', $adherentUuid->toString());
    }

    private function createEventRegistrationQueryBuilder(string $eventUuid): QueryBuilder
    {
        $uuid = Uuid::fromString($eventUuid);

        $qb = $this
            ->createQueryBuilder('r')
            ->select('r, e')
            ->leftJoin('r.event', 'e')
            ->where('e.uuid = :event_uuid')
            ->setParameter('event_uuid', $uuid->toString())
        ;

        return $qb;
    }
}
