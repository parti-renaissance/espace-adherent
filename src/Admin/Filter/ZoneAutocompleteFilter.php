<?php

namespace App\Admin\Filter;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;

class ZoneAutocompleteFilter extends CallbackFilter
{
    public function getDefaultOptions(): array
    {
        return [
            'label' => 'Périmètres géographiques',
            'show_filter' => true,
            'field_type' => ModelAutocompleteType::class,
            'field_options' => [],
            'operator_options' => [],
            'callback' => static function (ProxyQuery $qb, string $alias, string $field, array $value): bool {
                /* @var Collection|Zone[] $zones */
                $zones = $value['value'];

                if (0 === \count($zones)) {
                    return false;
                }

                $ids = $zones->map(static function (Zone $zone) {
                    return $zone->getId();
                })->toArray();

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

    public function getFieldOptions(): ?array
    {
        return array_merge([
            'context' => 'filter',
            'class' => Zone::class,
            'multiple' => true,
            'minimum_input_length' => 1,
            'items_per_page' => 20,
        ], parent::getFieldOptions());
    }
}
