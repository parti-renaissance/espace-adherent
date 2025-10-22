<?php

namespace App\JMEFilter\FilterBuilder;

use App\Entity\Geo\Zone;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\JMEFilter\FilterGroup\ZoneGeoFilterGroup;
use App\JMEFilter\Types\DefinedTypes\ZoneAutocomplete;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class ZoneAutocompleteFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, ?string $feature = null): bool
    {
        return true;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        $availableZoneTypes = $this->availableZoneTypes($scope);

        return (new FilterCollectionBuilder())
            ->createFrom(ZoneAutocomplete::class, [
                'code' => \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) ? 'zone' : 'zones',
                'zone_types' => \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) ? ($availableZoneTypes ?: [
                    Zone::BOROUGH,
                    Zone::CANTON,
                    Zone::CITY,
                    Zone::DEPARTMENT,
                    Zone::REGION,
                    Zone::COUNTRY,
                    Zone::DISTRICT,
                    Zone::FOREIGN_DISTRICT,
                    Zone::CUSTOM,
                ]) : [],
            ])
            ->setMultiple(!\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->setRequired(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) && ScopeEnum::ANIMATOR !== $scope)
            ->setHelp($availableZoneTypes ? ('<strong>Toutes les zones incluses dans votre zone de gestion sont filtrables.</strong> Exemple : '.implode(', ', array_map(fn (string $zoneType) => $this->translator->trans('geo_zone.'.$zoneType), $availableZoneTypes))) : null)
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        if (FeatureEnum::PUBLICATIONS === $feature) {
            return ZoneGeoFilterGroup::class;
        }

        return PersonalInformationsFilterGroup::class;
    }

    private function availableZoneTypes(string $scope): array
    {
        return match ($scope) {
            ScopeEnum::DEPUTY => [Zone::BOROUGH, Zone::CANTON, Zone::CITY],
            ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
                Zone::DISTRICT,
            ],
            ScopeEnum::REGIONAL_COORDINATOR => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
                Zone::DISTRICT,
                Zone::DEPARTMENT,
            ],
            ScopeEnum::REGIONAL_DELEGATE => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
                Zone::DISTRICT,
                Zone::DEPARTMENT,
            ],
            ScopeEnum::FDE_COORDINATOR => [
                Zone::COUNTRY,
                Zone::FOREIGN_DISTRICT,
                Zone::CUSTOM,
            ],
            ScopeEnum::CORRESPONDENT => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
                Zone::DISTRICT,
                Zone::DEPARTMENT,
            ],
            ScopeEnum::LEGISLATIVE_CANDIDATE => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
            ],
            ScopeEnum::SENATOR => [
                Zone::BOROUGH,
                Zone::CANTON,
                Zone::CITY,
            ],
            default => [],
        };
    }
}
