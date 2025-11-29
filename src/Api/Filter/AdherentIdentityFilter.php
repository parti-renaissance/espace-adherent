<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Pap\CampaignHistory as PapCampaignHistory;
use App\Entity\Phoning\CampaignHistory as PhoningCampaignHistory;
use Doctrine\ORM\QueryBuilder;

class AdherentIdentityFilter extends AbstractFilter
{
    private const PROPERTY_NAMES = ['adherent', 'caller', 'questioner'];
    private const OPERATION_NAMES = ['_api_/v3/phoning_campaign_histories_get_collection', '_api_/v3/pap_campaign_histories_get_collection'];

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            !\in_array($resourceClass, [PhoningCampaignHistory::class, PapCampaignHistory::class])
            || !\in_array($property, self::PROPERTY_NAMES, true)
            || empty($value)
            || !\in_array($operation->getName(), self::OPERATION_NAMES, true)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName($property);

        $queryBuilder
            ->leftJoin($alias.".$property", $property)
            ->andWhere(\sprintf('CONCAT(LOWER(%1$s.firstName), \' \', LOWER(%1$s.lastName)) LIKE :%2$s', $property, $parameterName))
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
