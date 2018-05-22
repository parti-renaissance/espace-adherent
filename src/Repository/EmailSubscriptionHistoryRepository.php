<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\Reporting\EmailSubscriptionHistoryAction;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EmailSubscriptionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

        $qb = $this->createQueryBuilder('history')
            ->select('history.adherentUuid, history.subscribedEmailType, history.action, COUNT(history) AS count')
            ->where('history.referentTag IN (:tags)')
            ->andWhere('history.subscribedEmailType IN (:subscriptions)')
            ->andWhere('history.date <= :until')
            ->groupBy('history.adherentUuid, history.subscribedEmailType, history.action')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('subscriptions', $subscriptionsTypes)
            ->setParameter('until', $until)
            ->getQuery()
        ;

        // Let's cache past data as they are never going to change
        $beginningOfThisMonth = (new Chronos('first day of this month'))->setTime(0, 0);
        if ($beginningOfThisMonth > $until) {
            $qb->useResultCache(true, 5184000); // 60 days
        }

        $results = $qb->getArrayResult();
        $countBySubscriptionType = [];

        foreach ($results as ['adherentUuid' => $adherentUuid, 'action' => $action, 'subscribedEmailType' => $type, 'count' => $count]) {
            $adherentUuid = (string) $adherentUuid;

            if (EmailSubscriptionHistoryAction::SUBSCRIBE === $action) {
                $countBySubscriptionType[$type][$adherentUuid] = ($countBySubscriptionType[$type][$adherentUuid] ?? 0) + $count;
            } elseif (EmailSubscriptionHistoryAction::UNSUBSCRIBE === $action) {
                $countBySubscriptionType[$type][$adherentUuid] = ($countBySubscriptionType[$type][$adherentUuid] ?? 0) - $count;
            } else {
                throw new \RuntimeException("'$action' is not handled");
            }
        }

        // If one adherent has multiple referent tags (With paris district for example), his email subscription must count for one and not many.
        // That's why we remove values equal to 0 and then count the number of entries we have by type.
        foreach ($subscriptionsTypes as $type) {
            if (isset($countBySubscriptionType[$type]) && \is_array($countBySubscriptionType[$type])) {
                $countBySubscriptionType[$type] = array_filter($countBySubscriptionType[$type]);
                $countBySubscriptionType[$type] = \count($countBySubscriptionType[$type]);
            } else {
                $countBySubscriptionType[$type] = 0;
            }
        }

        return $countBySubscriptionType;
    }
}
