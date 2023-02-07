<?php

namespace App\Filter\FilterBuilder;

use App\Entity\Geo\Zone;
use App\Filter\FilterCollectionBuilder;
use App\Filter\Types\DefinedTypes\ZoneAutocomplete;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class ZoneAutocompleteFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(ZoneAutocomplete::class, [
                'code' => FeatureEnum::MESSAGES === $feature ? 'zone' : 'zones',
                'zone_types' => FeatureEnum::MESSAGES === $feature ? [
                    Zone::BOROUGH,
                    Zone::CANTON,
                    Zone::CITY,
                    Zone::DEPARTMENT,
                    Zone::REGION,
                    Zone::COUNTRY,
                    Zone::DISTRICT,
                    Zone::FOREIGN_DISTRICT,
                ] : [],
            ])
            ->setMultiple(FeatureEnum::MESSAGES !== $feature)
            ->setRequired(FeatureEnum::MESSAGES === $feature)
            ->getFilters()
        ;
    }
}
