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

    /**
     * @return InstitutionalEvent[]
     */
    public function findByOrganizer(Adherent $referent): array
    {
        $this->checkReferent($referent);

        return $this
            ->createQueryBuilder('institutionalEvent')
            ->select('institutionalEvent', 'category', 'organizer')
            ->leftJoin('institutionalEvent.category', 'category')
            ->leftJoin('institutionalEvent.organizer', 'organizer')
            ->andWhere('institutionalEvent.published = true')
            ->andWhere('organizer = :organizer')
            ->orderBy('institutionalEvent.beginAt', 'DESC')
            ->addOrderBy('institutionalEvent.name', 'ASC')
            ->setParameter('organizer', $referent)
            ->getQuery()
            ->getResult()
        ;
    }
}
