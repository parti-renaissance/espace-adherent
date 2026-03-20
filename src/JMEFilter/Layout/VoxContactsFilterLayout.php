<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

use App\JMEFilter\FilterBuilder\AdherentTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\BasicFieldsFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeFilterBuilder;
use App\JMEFilter\FilterBuilder\CommitteeMemberFilterBuilder;
use App\JMEFilter\FilterBuilder\ContributionDatesFilterBuilder;
use App\JMEFilter\FilterBuilder\DeclaredMandateFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectMandatesFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\EmailSubscriptionStatusFilterBuilder;
use App\JMEFilter\FilterBuilder\SearchTermFilterBuilder;
use App\JMEFilter\FilterBuilder\SmsSubscriptionStatusFilterBuilder;
use App\JMEFilter\FilterBuilder\StaticTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\ZoneAutocompleteFilterBuilder;
use App\JMEFilter\FilterGroup\CommunicationsFilterGroup;
use App\JMEFilter\FilterGroup\DatesFilterGroup;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\JMEFilter\FilterGroup\EmptyGroup;
use App\JMEFilter\FilterGroup\LocalizationFilterGroup;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\Scope\FeatureEnum;

class VoxContactsFilterLayout extends AbstractFilterLayout
{
    public function supports(string $scope, ?string $feature, bool $isVox): bool
    {
        return $isVox && FeatureEnum::CONTACTS === $feature;
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getGroupConfigs(string $scope): array
    {
        return [
            $this->group(EmptyGroup::class, [
                $this->filter(SearchTermFilterBuilder::class),
            ]),
            $this->group(EmptyGroup::class, [
                $this->filter(AdherentTagsFilterBuilder::class),
                $this->filter(StaticTagsFilterBuilder::class),
            ]),
            $this->group(LocalizationFilterGroup::class, [
                $this->filter(ZoneAutocompleteFilterBuilder::class),
                $this->filter(CommitteeMemberFilterBuilder::class),
                $this->filter(CommitteeFilterBuilder::class),
            ]),
            $this->group(PersonalInformationsFilterGroup::class, [
                $this->filter(BasicFieldsFilterBuilder::class),
            ]),
            $this->group(CommunicationsFilterGroup::class, [
                $this->filter(EmailSubscriptionStatusFilterBuilder::class),
                $this->filter(SmsSubscriptionStatusFilterBuilder::class),
            ]),
            $this->group(DatesFilterGroup::class, [
                $this->filter(ContributionDatesFilterBuilder::class),
            ], 'Dates'),
            $this->group(ElectedRepresentativeFilterGroup::class, [
                $this->filter(ElectTagsFilterBuilder::class),
                $this->filter(ElectMandatesFilterBuilder::class),
                $this->filter(DeclaredMandateFilterBuilder::class),
            ], 'Élus'),
        ];
    }
}
