<?php

namespace App\Admin\Filter;

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdherentTagFilter extends AbstractCallbackDecoratorFilter
{
    protected function getInitialFilterOptions(): array
    {
        return [
            'field_type' => ChoiceType::class,
            'operator_type' => ContainsOperatorType::class,
            'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                if (!$value->hasValue()) {
                    return false;
                }

                $orX = $qb->expr()->orX();

                $condition = match ($value->getType()) {
                    ContainsOperatorType::TYPE_NOT_CONTAINS => 'NOT LIKE',
                    default => 'LIKE',
                };

                foreach ($value->getValue() as $index => $choice) {
                    $orX->add($alias.'.tags '.$condition.' :tag_'.$field.'_'.$index);
                    $qb->setParameter('tag_'.$field.'_'.$index, '%'.$choice.'%');
                }

                $qb->andWhere($orX);

                return true;
            },
        ];
    }
}
