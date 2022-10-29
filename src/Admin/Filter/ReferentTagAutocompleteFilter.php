<?php

namespace App\Admin\Filter;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class ReferentTagAutocompleteFilter extends AbstractCallbackDecoratorFilter
{
    protected function getInitialFilterOptions(): array
    {
        return [
            'show_filter' => true,
            'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                if (!$value->hasValue()) {
                    return false;
                }

                /** @var QueryBuilder $qb */
                $qb
                    ->leftJoin("$alias.$field", 'managed_area')
                    ->leftJoin('managed_area.tags', 'tags')
                    ->andWhere('tags IN (:tags)')
                    ->setParameter('tags', $value->getValue())
                ;

                return true;
            },
        ];
    }
}
