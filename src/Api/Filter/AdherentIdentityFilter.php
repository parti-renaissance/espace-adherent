<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Pap\CampaignHistory as PapCampaignHistory;
use App\Entity\Phoning\CampaignHistory as PhoningCampaignHistory;
use Doctrine\ORM\QueryBuilder;

class AdherentIdentityFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAMES = ['adherent', 'caller', 'questioner'];
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
            !\in_array($resourceClass, [PhoningCampaignHistory::class, PapCampaignHistory::class])
            || !\in_array($property, self::PROPERTY_NAMES, true)
            || empty($value)
            || !\in_array($operationName, self::OPERATION_NAMES, true)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName($property);

        $queryBuilder
            ->leftJoin($alias.".$property", $property)
            ->andWhere(sprintf('CONCAT(LOWER(%1$s.firstName), \' \', LOWER(%1$s.lastName)) LIKE :%2$s', $property, $parameterName))
            ->setParameter($parameterName, '%'.strtolower($value).'%')
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description[$property] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }
}
