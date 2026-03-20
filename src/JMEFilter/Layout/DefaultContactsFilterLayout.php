<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

use App\JMEFilter\FilterBuilder\AdherentTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\BasicFieldsFilterBuilder;
use App\JMEFilter\FilterBuilder\ContributionDatesFilterBuilder;
use App\JMEFilter\FilterBuilder\DeclaredMandateFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectMandatesFilterBuilder;
use App\JMEFilter\FilterBuilder\ElectTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\EmailSubscriptionStatusFilterBuilder;
use App\JMEFilter\FilterBuilder\MilitantFilterBuilder;
use App\JMEFilter\FilterBuilder\NameFilterBuilder;
use App\JMEFilter\FilterBuilder\SearchTermFilterBuilder;
use App\JMEFilter\FilterBuilder\SmsSubscriptionStatusFilterBuilder;
use App\JMEFilter\FilterBuilder\StaticTagsFilterBuilder;
use App\JMEFilter\FilterBuilder\ZoneAutocompleteFilterBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\JMEFilter\FilterGroup\EmptyGroup;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\Scope\FeatureEnum;

class DefaultContactsFilterLayout extends AbstractFilterLayout
{
    public function supports(string $scope, ?string $feature, bool $isVox): bool
    {
        return !$isVox && FeatureEnum::CONTACTS === $feature;
    }

    public function getGroupConfigs(string $scope): array
    {
        return [
            $this->group(EmptyGroup::class, [
                $this->filter(SearchTermFilterBuilder::class),
            ]),
            $this->group(PersonalInformationsFilterGroup::class, [
                $this->filter(BasicFieldsFilterBuilder::class),
                $this->filter(EmailSubscriptionStatusFilterBuilder::class),
                $this->filter(NameFilterBuilder::class),
                $this->filter(SmsSubscriptionStatusFilterBuilder::class),
                $this->filter(ZoneAutocompleteFilterBuilder::class),
            ]),
            $this->group(MilitantFilterGroup::class, [
                $this->filter(AdherentTagsFilterBuilder::class),
                $this->filter(StaticTagsFilterBuilder::class),
                $this->filter(ContributionDatesFilterBuilder::class),
                $this->filter(MilitantFilterBuilder::class),
            ]),
            $this->group(ElectedRepresentativeFilterGroup::class, [
                $this->filter(DeclaredMandateFilterBuilder::class),
                $this->filter(ElectTagsFilterBuilder::class),
                $this->filter(ElectMandatesFilterBuilder::class),
            ]),
        ];
    }
}
