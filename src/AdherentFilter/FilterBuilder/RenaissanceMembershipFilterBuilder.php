<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Committee\Filter\Enum\RenaissanceMembershipFilterEnum;
use App\Filter\FilterCollectionBuilder;

class RenaissanceMembershipFilterBuilder implements AdherentFilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return true;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('renaissance_membership', 'Renaissance')
            ->setChoices(array_flip(RenaissanceMembershipFilterEnum::CHOICES))
            ->getFilters()
        ;
    }
}
