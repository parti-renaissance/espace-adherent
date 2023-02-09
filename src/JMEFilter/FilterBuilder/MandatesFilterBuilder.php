<?php

namespace App\JMEFilter\FilterBuilder;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class MandatesFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return FeatureEnum::ELECTED_REPRESENTATIVE === $feature;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('mandates', 'Mandats')
            ->setChoices(array_flip(MandateTypeEnum::CHOICES))
            ->setMultiple(true)
            ->getFilters()
        ;
    }
}
