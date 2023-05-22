<?php

namespace App\JMEFilter\FilterBuilder;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\Scope\FeatureEnum;

class MandateTypeFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return FeatureEnum::ELECTED_REPRESENTATIVE === $feature;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('mandateTypes', 'Type de mandat')
            ->setChoices(array_flip(MandateTypeEnum::TYPE_CHOICES))
            ->setMultiple(true)
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
