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
        return \in_array($feature, [
            FeatureEnum::CONTACTS,
            FeatureEnum::MESSAGES,
        ], true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('mandateTypes', 'Type de mandat')
            ->setChoices(array_flip(MandateTypeEnum::CHOICES))
            ->setMultiple(true)
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
