<?php

namespace App\Utils;

use App\Statistics\StatisticsParametersFilter;
use Cake\Chronos\Chronos;
use Doctrine\ORM\QueryBuilder;

class RepositoryUtils
{
    public static function addStatstFilter(
        StatisticsParametersFilter $filter,
        QueryBuilder $query,
        string $aliasForCommittee = 'event',
        string $aliasForPostAddress = 'event'
    ): QueryBuilder {
        if ($filter->getCommittee()) {
            $query->andWhere("$aliasForCommittee.committee = :committee")
                ->setParameter('committee', $filter->getCommittee())
            ;
        }

        if ($filter->getCityName()) {
            $query->andWhere("$aliasForPostAddress.postAddress.cityName = :city")
                ->setParameter('city', $filter->getCityName())
            ;
        }

        if ($filter->getCountryCode()) {
            $query->andWhere("$aliasForPostAddress.postAddress.country = :country")
                ->setParameter('country', $filter->getCountryCode())
            ;
        }

        return $query;
    }

    public static function aggregateCountByMonth(array $itemsCount, int $months = 6): array
    {
        foreach (range(0, $months - 1) as $month) {
            $until = (new Chronos("first day of -$month month"));
            $countByMonth[$until->format('Y-m')] = ['date' => $until->format('Y-m'), 'count' => 0];
            foreach ($itemsCount as $count) {
                if ($until->format('Ym') === $count['yearmonth']) {
                    $countByMonth[$until->format('Y-m')]['count'] += $count['count'];
                }
            }
        }

        return array_values($countByMonth);
    }
}
