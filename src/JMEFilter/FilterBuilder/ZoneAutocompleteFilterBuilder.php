<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\AdherentMessage\PublicationZone;
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
        $availableZoneTypes = PublicationZone::availableZoneTypes($scope);

        return (new FilterCollectionBuilder())
            ->createFrom(ZoneAutocomplete::class, [
                'code' => \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) ? 'zone' : 'zones',
                'zone_types' => \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) ? ($availableZoneTypes ?: PublicationZone::ZONE_TYPES) : [],
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
}
