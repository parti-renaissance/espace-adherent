<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    use NearbyTrait;

    public function findOneBySlug(string $slug): ?Event
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e, c, o')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->andWhere('c.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findMostRecentEvent(): ?Event
    {
        $query = $this
            ->createQueryBuilder('ce')
            ->orderBy('ce.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findOneByUuid(string $uuid): ?Event
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findManagedBy(Adherent $referent)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->orderBy('e.beginAt', 'DESC')
            ->addOrderBy('e.name', 'ASC');

        $codesFilter = $qb->expr()->orX();

        foreach ($referent->getManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add($qb->expr()->like('e.postAddress.postalCode', ':code'.$key));
                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('e.postAddress.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);

        return $qb->getQuery()->getResult();
    }
}
