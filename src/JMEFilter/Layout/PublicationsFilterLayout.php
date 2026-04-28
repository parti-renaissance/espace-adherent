<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

use App\JMEFilter\FilterBuilder\AdherentTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\BasicFieldsFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeMemberFilterBuilder;
use App\JMEFilter\FilterBuilder\ContributionDatesFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectMandatesFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\MilitantFilterBuilder;
use App\JMEFilter\FilterBuilder\ScopeTargetFilterBuilder;
use App\JMEFilter\FilterBuilder\StaticTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\ZoneAutocompleteFilterBuilder;
use App\JMEFilter\FilterGroup\DatesFilterGroup;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\JMEFilter\FilterGroup\ScopeTargetFilterGroup;
use App\JMEFilter\FilterGroup\ZoneGeoFilterGroup;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;

class PublicationsFilterLayout extends AbstractFilterLayout
{
    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function supports(string $scope, ?string $feature, bool $isVox): bool
    {
        return FeatureEnum::PUBLICATIONS === $feature;
    }

    public function getGroupConfigs(string $scope): array
    {
        $groups = [
            $this->group(ZoneGeoFilterGroup::class, [
                $this->filter(ZoneAutocompleteFilterBuilder::class),
            ]),
            $this->group(MilitantFilterGroup::class, [
                $this->filter(AdherentTagsFilterBuilder::class),
                $this->filter(BasicFieldsFilterBuilder::class),
                $this->filter(CommitteeFilterBuilder::class),
                $this->filter(CommitteeMemberFilterBuilder::class),
                $this->filter(StaticTagsFilterBuilder::class),
            ]),
            $this->group(DatesFilterGroup::class, [
                $this->filter(MilitantFilterBuilder::class),
                $this->filter(ContributionDatesFilterBuilder::class),
            ]),
        ];

        if ($this->scopeGeneratorResolver->generate()?->hasFeature(FeatureEnum::PUBLICATIONS_CADRES)) {
            $groups[] = $this->group(ScopeTargetFilterGroup::class, [
                $this->filter(ScopeTargetFilterBuilder::class),
            ]);
        }

        $groups[] = $this->group(ElectedRepresentativeFilterGroup::class, [
            $this->filter(ElectTagsFilterBuilder::class),
            $this->filter(ElectMandatesFilterBuilder::class),
        ]);

        return $groups;
    }
}
