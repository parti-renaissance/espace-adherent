<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\Reporting\EmailSubscriptionHistoryAction;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class EmailSubscriptionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailSubscriptionHistory::class);
    }

    /**
     * Count active adherents (not users) history for each specified subscription types in the referent managed area before the specified date.
     */
    public function countAllByTypeForReferentManagedArea(Adherent $referent, array $subscriptionsTypes, \DateTime $until): array
    {
        if (!$referent->isReferent()) {
            throw new \InvalidArgumentException('Adherent must be a referent.');
        }

        $qb = $this->createQueryBuilder('history')
            ->select('history.subscribedEmailType, history.action, COUNT(history) AS count')
            ->andWhere('history.referentTag IN (:tags)')
            ->andWhere('history.subscribedEmailType IN (:subscriptions)')
            ->andWhere('history.date <= :until')
            ->groupBy('history.subscribedEmailType, history.action')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('subscriptions', $subscriptionsTypes)
            ->setParameter('until', $until)
            ->getQuery()
        ;

        // Let's cache past data as they are never going to change
        $firstDayOfMonth = (new Carbon('first day of this month'))->setTime(0, 0);
        if ($firstDayOfMonth > $until) {
            $qb->useResultCache(true, 5184000); // 60 days
        }

        $results = $qb->getArrayResult();
        $countBySubscriptionType = array_fill_keys($subscriptionsTypes, 0);

        foreach ($results as ['action' => $action, 'subscribedEmailType' => $type, 'count' => $count]) {
            if (EmailSubscriptionHistoryAction::SUBSCRIBE === $action) {
                $countBySubscriptionType[$type] += $count;
            } elseif (EmailSubscriptionHistoryAction::UNSUBSCRIBE === $action) {
                $countBySubscriptionType[$type] -= $count;
            } else {
                throw new \RuntimeException("'$action' is not handled");
            }
        }

        return $countBySubscriptionType;
    }
}
