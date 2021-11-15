<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Phoning\CampaignHistory;
use Doctrine\ORM\QueryBuilder;

class PhoningCampaignCallerFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'caller';
    private const OPERATION_NAMES = ['get'];

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            CampaignHistory::class !== $resourceClass
            || self::PROPERTY_NAME !== $property
            || empty($value)
            || !\in_array($operationName, self::OPERATION_NAMES, true)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin($alias.'.caller', 'caller')
            ->andWhere('CONCAT(LOWER(caller.firstName), \' \', LOWER(caller.lastName)) LIKE :caller')
            ->setParameter('caller', '%'.strtolower($value).'%')
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY_NAME => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
