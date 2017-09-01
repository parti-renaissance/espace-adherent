<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\QueryBuilder;

class CitizenInitiativeRepository extends EventRepository
{
    const TYPE_PAST = 'past';
    const TYPE_UPCOMING = 'upcoming';
    const TYPE_ALL = 'all';

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

    public function removeOrganizerCitizenInitiatives(Adherent $organizer, string $type = self::TYPE_ALL, $anonymize = false)
    {
        $type = strtolower($type);
        $qb = $this->createQueryBuilder('e');
        if ($anonymize) {
            $qb->update()
                ->set('e.organizer', ':new_value')
                ->setParameter('new_value', null);
        } else {
            $qb->delete()
                ->set('e.organizer', $qb->expr()->literal(null));
        }

        $qb->where('e.organizer = :organizer')
            ->setParameter('organizer', $organizer);

        if (in_array($type, [self::TYPE_UPCOMING, self::TYPE_PAST], true)) {
            if (self::TYPE_PAST === $type) {
                $qb->andWhere('e.finishAt <= :date');
            } else {
                $qb->andWhere('e.beginAt >= :date');
            }
            // The extra 24 hours enable to include events in foreign
            // countries that are on different timezones.
            $qb->setParameter('date', new \DateTime('-24 hours'));
        }

        return $qb->getQuery()->execute();
    }
}
