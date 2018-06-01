<?php

namespace AppBundle\History;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\CommitteeMembershipHistoryRepository;
use AppBundle\Statistics\StatisticsParametersFilter;
use Cake\Chronos\Chronos;

class CommitteeMembershipHistoryHandler
{
    private $historyRepository;

    public function __construct(CommitteeMembershipHistoryRepository $historyRepository)
    {
        $this->historyRepository = $historyRepository;
    }

    public function queryCountByMonth(Adherent $referent, int $months = 6, StatisticsParametersFilter $filter = null, string $keyName = 'in_at_least_one_committee'): array
    {
        foreach (range(0, $months - 1) as $monthInterval) {
            $until = $monthInterval
                        ? (new Chronos("last day of -$monthInterval month"))->setTime(23, 59, 59, 999)
                        : new Chronos()
            ;

            $count = $this->historyRepository->countAdherentMemberOfAtLeastOneCommitteeManagedBy($referent, $until, $filter);

            $countByMonth[$until->format('Y-m')] = [$keyName => $count];
        }

        return $countByMonth;
    }
}
