<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Donation\DonatorStatusEnum;
use App\JMEFilter\FilterCollectionBuilder;

class DonatorStatusFilterBuilder implements FilterBuilderInterface
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createSelect('donator_status', 'Donateur')
            ->setChoices([
                DonatorStatusEnum::DONATOR_N => 'Donateur année en cours',
                DonatorStatusEnum::DONATOR_N_X => 'Donateur années passées uniquement',
                DonatorStatusEnum::NOT_DONATOR => 'Pas encore donateur',
            ])
            ->getFilters()
        ;
    }
}
