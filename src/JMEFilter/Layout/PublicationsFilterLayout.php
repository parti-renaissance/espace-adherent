<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

use App\JMEFilter\FilterBuilder\AdherentTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\BasicFieldsFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeMemberFilterBuilder;
use App\JMEFilter\FilterBuilder\ContributionDatesFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\MandatesFilterBuilder;
use App\JMEFilter\FilterBuilder\MilitantFilterBuilder;
use App\JMEFilter\FilterBuilder\StaticTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\ZoneAutocompleteFilterBuilder;
use App\JMEFilter\FilterGroup\DatesFilterGroup;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\JMEFilter\FilterGroup\ZoneGeoFilterGroup;
use App\Scope\FeatureEnum;

class PublicationsFilterLayout extends AbstractFilterLayout
{
    public function supports(string $scope, ?string $feature, bool $isVox): bool
    {
        return FeatureEnum::PUBLICATIONS === $feature;
    }

    public function getGroupConfigs(): array
    {
        return [
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
            $this->group(ElectedRepresentativeFilterGroup::class, [
                $this->filter(ElectTagsFilterBuilder::class),
                $this->filter(MandatesFilterBuilder::class),
            ]),
        ];
    }
}
