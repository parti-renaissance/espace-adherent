<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\InstitutionalEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;

class InstitutionalEventRepository extends EventRepository
{
    use ReferentTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InstitutionalEvent::class);
    }

    public function findOneInstitutionalEventBySlug(string $slug): ?InstitutionalEvent
    {
        $query = $this
            ->createQueryBuilder('institutionalEvent')
            ->select('institutionalEvent', 'category', 'organizer')
            ->leftJoin('institutionalEvent.institutionalEventCategory', 'category')
            ->leftJoin('institutionalEvent.organizer', 'organizer')
            ->where('institutionalEvent.slug = :slug')
            ->andWhere('institutionalEvent.published = :published')
            ->setParameter('slug', $slug)
            ->setParameter('published', true)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * @return InstitutionalEvent[]
     */
    public function findByOrganizer(Adherent $referent): array
    {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('institutionalEvent')
            ->select('institutionalEvent', 'category', 'organizer')
            ->leftJoin('institutionalEvent.category', 'category')
            ->leftJoin('institutionalEvent.organizer', 'organizer')
            ->where('institutionalEvent.published = :published')
            ->andWhere('organizer = :organizer')
            ->orderBy('institutionalEvent.beginAt', 'DESC')
            ->addOrderBy('institutionalEvent.name', 'ASC')
            ->setParameter('organizer', $referent)
            ->setParameter('published', true)
        ;

        return $qb->getQuery()->getResult();
    }
}
