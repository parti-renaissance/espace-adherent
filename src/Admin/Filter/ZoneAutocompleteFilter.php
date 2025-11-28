<?php

declare(strict_types=1);

namespace App\Admin\Filter;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class ZoneAutocompleteFilter extends AbstractCallbackDecoratorFilter
{
    protected function getInitialFilterOptions(): array
    {
        return [
            'show_filter' => true,
            'callback' => static function (ProxyQuery $qb, string $alias, string $field, FilterData $data): bool {
                if (!$data->hasValue()) {
                    return false;
                }

                $zones = $data->getValue();

                if ($zones instanceof Collection) {
                    $zones = $zones->toArray();
                } elseif (!\is_array($zones)) {
                    $zones = [$zones];
                }

                $ids = array_map(static function (Zone $zone) {
                    return $zone->getId();
                }, $zones);

                /* @var QueryBuilder $qb */
                $qb
                    ->innerJoin("$alias.$field", 'zone_filter')
                    ->leftJoin('zone_filter.parents', 'zone_parent_filter')
                    ->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->in('zone_filter.id', $ids),
                            $qb->expr()->in('zone_parent_filter.id', $ids),
                        )
                    )
                ;

                return true;
            },
        ];
    }
}
