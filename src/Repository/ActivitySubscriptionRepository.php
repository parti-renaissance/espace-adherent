<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ActivitySubscription;
use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityRepository;

class ActivitySubscriptionRepository extends EntityRepository
{
    public function findSubscription(Adherent $following, Adherent $followed): ?ActivitySubscription
    {
        return $this->createQueryBuilder('s')
            ->where('s.followingAdherent = :following')
            ->andWhere('s.followedAdherent = :followed')
            ->setParameters([
                'following' => $following,
                'followed' => $followed,
            ])
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function removeAdherentActivities(Adherent $adherent)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->delete()
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('s.followingAdherent', ':adherent'),
                    $qb->expr()->eq('s.followedAdherent', ':adherent')
                )
            )
            ->setParameter(':adherent', $adherent);

        return $qb->getQuery()->execute();
    }
}
