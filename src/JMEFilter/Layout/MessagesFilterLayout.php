<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

use App\JMEFilter\FilterBuilder\AdherentTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\BasicFieldsFilterBuilder;
use App\JMEFilter\FilterBuilder\CertifiedStatusFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeMemberFilterBuilder;
use App\JMEFilter\FilterBuilder\ContributionDatesFilterBuilder;
use App\JMEFilter\FilterBuilder\DeclaredMandateFilterBuilder;
use App\JMEFilter\FilterBuilder\DonatorStatusFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectMandatesFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\MilitantFilterBuilder;
use App\JMEFilter\FilterBuilder\NameFilterBuilder;
use App\JMEFilter\FilterBuilder\ScopeTargetFilterBuilder;
use App\JMEFilter\FilterBuilder\StaticTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\ZoneAutocompleteFilterBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\JMEFilter\FilterGroup\ScopeTargetFilterGroup;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class MessagesFilterLayout extends AbstractFilterLayout
{
    public function supports(string $scope, ?string $feature, bool $isVox): bool
    {
        return FeatureEnum::MESSAGES === $feature;
    }

    public function getGroupConfigs(string $scope): array
    {
        $isNational = ScopeEnum::isNational($scope);

        $personalFilters = [
            $this->filter(BasicFieldsFilterBuilder::class),
            $this->filter(NameFilterBuilder::class),
            $this->filter(ZoneAutocompleteFilterBuilder::class),
        ];

        if ($isNational) {
            $personalFilters[] = $this->filter(CertifiedStatusFilterBuilder::class);
        }

        $groups = [
            $this->group(PersonalInformationsFilterGroup::class, $personalFilters),
            $this->group(MilitantFilterGroup::class, [
                $this->filter(AdherentTagsFilterBuilder::class),
                $this->filter(CommitteeFilterBuilder::class),
                $this->filter(CommitteeMemberFilterBuilder::class),
                $this->filter(DonatorStatusFilterBuilder::class),
                $this->filter(StaticTagsFilterBuilder::class),
                $this->filter(MilitantFilterBuilder::class),
                $this->filter(ContributionDatesFilterBuilder::class),
            ]),
        ];

        if ($isNational) {
            $groups[] = $this->group(ScopeTargetFilterGroup::class, [
                $this->filter(ScopeTargetFilterBuilder::class),
            ]);
        }

        $groups[] = $this->group(ElectedRepresentativeFilterGroup::class, [
            $this->filter(DeclaredMandateFilterBuilder::class),
            $this->filter(ElectTagsFilterBuilder::class),
            $this->filter(ElectMandatesFilterBuilder::class),
        ]);

        return $groups;
    }
}
