<?php

namespace App\ElectedRepresentative\Filter\FilterBuilder;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Filter\FilterCollectionBuilder;
use App\Scope\ScopeEnum;

class MandatesFilterBuilder implements ElectedRepresentativeFilterBuilderInterface
{
    public function supports(string $scope): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('mandates', 'Mandats')
            ->setChoices(array_flip(MandateTypeEnum::CHOICES))
            ->setMultiple(true)
            ->getFilters()
        ;
    }
}
