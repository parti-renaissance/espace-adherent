<?php

declare(strict_types=1);

namespace App\Admin\Filter;

use App\Doctrine\Utils\MultiColumnsSearchHelper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdherentSearchFilter extends AbstractCallbackDecoratorFilter
{
    protected function getInitialFilterOptions(): array
    {
        return [
            'show_filter' => true,
            'field_type' => TextType::class,
            'callback' => static function (ProxyQuery $qb, string $alias, string $field, FilterData $data): bool {
                $searchTerm = $data->hasValue() ? $data->getValue() : null;
                if (!\is_string($searchTerm) || '' === trim($searchTerm)) {
                    return false;
                }
                $queryBuilder = $qb->getQueryBuilder();
                $queryBuilder->leftJoin("$alias.$field", 'adherent_search');

                MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                    $queryBuilder,
                    $searchTerm,
                    [
                        ['adherent_search.firstName', 'adherent_search.lastName'],
                        ['adherent_search.lastName', 'adherent_search.firstName'],
                        ['adherent_search.emailAddress', 'adherent_search.emailAddress'],
                    ],
                    [],
                    [
                        'adherent_search.id',
                        'adherent_search.uuid',
                        'adherent_search.publicId',
                    ]
                );

                return true;
            },
        ];
    }
}
