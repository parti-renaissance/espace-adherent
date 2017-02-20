<?php

namespace AppBundle\Repository;

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
