<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Donation\DonatorStatusEnum;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class DonatorStatusFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY === $scope && FeatureEnum::MESSAGES === $feature;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return new FilterCollectionBuilder()
            ->createSelect('donatorStatus', 'Donateur')
            ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature)
            ->setChoices([
                DonatorStatusEnum::DONATOR_N => 'Donateur année en cours',
                DonatorStatusEnum::DONATOR_N_X => 'Donateur années passées uniquement',
                DonatorStatusEnum::NOT_DONATOR => 'Pas encore donateur',
            ])
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return MilitantFilterGroup::class;
    }
}
