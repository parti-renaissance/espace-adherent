<?php

namespace App\JMEFilter\FilterBuilder;

use App\Entity\Geo\Zone;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\JMEFilter\Types\DefinedTypes\ZoneAutocomplete;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class ZoneAutocompleteFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(ZoneAutocomplete::class, [
                'code' => \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]) ? 'zone' : 'zones',
                'zone_types' => \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]) ? [
                    Zone::BOROUGH,
                    Zone::CANTON,
                    Zone::CITY,
                    Zone::DEPARTMENT,
                    Zone::REGION,
                    Zone::COUNTRY,
                    Zone::DISTRICT,
                    Zone::FOREIGN_DISTRICT,
                    Zone::CUSTOM,
                ] : [],
            ])
            ->setMultiple(!\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]))
            ->setRequired(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]) && ScopeEnum::ANIMATOR !== $scope)
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return PersonalInformationsFilterGroup::class;
    }
}
