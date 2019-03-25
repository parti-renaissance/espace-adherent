<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipHistory;
use AppBundle\Statistics\StatisticsParametersFilter;
use AppBundle\Utils\RepositoryUtils;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class CommitteeMembershipHistoryRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeMembershipHistory::class);
    }

    public function countAdherentMemberOfAtLeastOneCommitteeManagedBy(
        Adherent $referent,
        \DateTimeInterface $until,
        StatisticsParametersFilter $filter = null
    ): int {
        $this->checkReferent($referent);

        $query = $this->createQueryBuilder('history')
            ->select('history.action, history.adherentUuid, COUNT(history) AS count')
            ->join('history.referentTags', 'tags')
            ->where('tags IN (:tags)')
            ->andWhere('history.date <= :until')
            ->groupBy('history.action, history.adherentUuid')
            ->setParameter('tags', $referent->getAdherentReferentData()->getTags())
            ->setParameter('until', $until)
        ;

        if ($filter) {
            $query->leftJoin('history.committee', 'committee');
            RepositoryUtils::addStatstFilter($filter, $query, 'history', 'committee');
        }
        $query = $query->getQuery();

        // Let's cache past data as they are never going to change
        $firstDayOfMonth = (new Chronos('first day of this month'))->setTime(0, 0);
        if ($firstDayOfMonth > $until) {
            $query->useResultCache(true, 5184000); // 60 days
        }

        $results = $query->getArrayResult();
        $countByAdherent = [];

        /** @var UuidInterface $uuid */
        foreach ($results as ['count' => $count, 'action' => $action, 'adherentUuid' => $uuid]) {
            $uuid = $uuid->toString();

            if (CommitteeMembershipAction::LEAVE === $action) {
                $countByAdherent[$uuid] = ($countByAdherent[$uuid] ?? 0) - $count;
            } elseif (CommitteeMembershipAction::JOIN === $action) {
                $countByAdherent[$uuid] = ($countByAdherent[$uuid] ?? 0) + $count;
            } else {
                throw new \RuntimeException("'$action' is not handled");
            }
        }

        return \count(array_filter($countByAdherent));
    }
}
