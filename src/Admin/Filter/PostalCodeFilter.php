<?php

declare(strict_types=1);

namespace App\Admin\Filter;

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\StringOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PostalCodeFilter extends AbstractCallbackDecoratorFilter
{
    protected function getInitialFilterOptions(): array
    {
        return [
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

                $postalCodes = array_map('trim', explode(',', strtolower($value->getValue())));

                $postalCodeExpression = $qb->expr()->orX();
                foreach (array_filter($postalCodes) as $key => $code) {
                    $postalCodeExpression->add("$alias.postAddress.postalCode $condition :postalCode_$key");
                    $qb->setParameter(
                        'postalCode_'.$key,
                        match ($value->getType()) {
                            StringOperatorType::TYPE_EQUAL,
                            StringOperatorType::TYPE_NOT_EQUAL => $code,
                            StringOperatorType::TYPE_STARTS_WITH => "$code%",
                            StringOperatorType::TYPE_ENDS_WITH => "%$code",
                            default => "%$code%",
                        }
                    );
                }

                $qb->andWhere($postalCodeExpression);

                return true;
            },
        ];
    }
}
