<?php

namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class CitizenInitiativeRepository extends EventRepository
{
    protected function createUuidQueryBuilder(string $uuid): QueryBuilder
    {
        self::validUuid($uuid);

        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'o')
            ->leftJoin('e.citizenInitiativeCategory', 'a')
            ->leftJoin('e.organizer', 'o')
            ->where('e.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->andWhere('e.published = :published')
            ->setParameter('published', true)
            ;
    }
}
