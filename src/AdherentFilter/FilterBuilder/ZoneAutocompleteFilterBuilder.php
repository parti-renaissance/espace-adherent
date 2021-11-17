<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Entity\Geo\Zone;
use App\Filter\FilterCollectionBuilder;
use App\Filter\Types\DefinedTypes\ZoneAutocomplete;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class ZoneAutocompleteFilterBuilder implements AdherentFilterBuilderInterface
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
                    Zone::CANTON,
                    Zone::CITY,
                    Zone::DEPARTMENT,
                    Zone::REGION,
                    Zone::COUNTRY,
                ] : [],
            ])
            ->setMultiple(FeatureEnum::MESSAGES !== $feature)
            ->setRequired(FeatureEnum::MESSAGES === $feature)
            ->getFilters()
        ;
    }
}
