<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\Reporting\EmailSubscriptionHistoryAction;
use Cake\Chronos\Chronos;
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
    public function countAllByTypeForReferentManagedArea(Adherent $referent, array $subscriptionsTypes, \DateTimeInterface $until): array
    {
        if (!$referent->isReferent()) {
            throw new \InvalidArgumentException('Adherent must be a referent.');
        }

        $qb = $this->createQueryBuilder('history', 'history.subscribedEmailType')
            ->select('history.subscribedEmailType')
            ->addSelect('SUM(CASE WHEN history.action = :subscribe THEN 1 ELSE -1 END) AS total')
            ->where('history.referentTag IN (:tags)')
            ->andWhere('history.subscribedEmailType IN (:subscriptions)')
            ->andWhere('history.date <= :until')
            ->groupBy('history.subscribedEmailType')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('subscriptions', $subscriptionsTypes)
            ->setParameter('until', $until)
            ->setParameter('subscribe', EmailSubscriptionHistoryAction::SUBSCRIBE)
            ->getQuery()
        ;

        // Let's cache past data as they are never going to change
        $beginningOfThisMonth = (new Chronos('first day of this month'))->setTime(0, 0);
        if ($beginningOfThisMonth > $until) {
            $qb->useResultCache(true, 5184000); // 60 days
        }

        $result = $qb->getArrayResult();

        $countBySubscriptionType = [];
        foreach ($subscriptionsTypes as $type) {
            $countBySubscriptionType[$type] = isset($result[$type]['total']) ? (int) $result[$type]['total'] : 0;
        }

        return $countBySubscriptionType;
    }
}
