<?php

declare(strict_types=1);

namespace App\Admin\Filter;

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\StringOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UtmFilter extends AbstractCallbackDecoratorFilter
{
    protected function getInitialFilterOptions(): array
    {
        return [
            'label' => 'UTM Source / Campagne',
            'field_type' => TextType::class,
            'operator_type' => StringOperatorType::class,
            'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                if (!$value->hasValue()) {
                    return false;
                }

                $condition = match ($value->getType()) {
                    StringOperatorType::TYPE_NOT_CONTAINS => 'NOT LIKE',
                    StringOperatorType::TYPE_EQUAL => '=',
                    StringOperatorType::TYPE_NOT_EQUAL => '!=',
                    default => 'LIKE',
                };

                $utmValue = $value->getValue();

                $qb
                    ->andWhere("($alias.utmSource $condition :utm_value OR $alias.utmCampaign $condition :utm_value)")
                    ->setParameter(
                        'utm_value',
                        match ($value->getType()) {
                            StringOperatorType::TYPE_EQUAL,
                            StringOperatorType::TYPE_NOT_EQUAL => $utmValue,
                            StringOperatorType::TYPE_STARTS_WITH => "$utmValue%",
                            StringOperatorType::TYPE_ENDS_WITH => "%$utmValue",
                            default => "%$utmValue%",
                        }
                    )
                ;

                return true;
            },
        ];
    }
}
