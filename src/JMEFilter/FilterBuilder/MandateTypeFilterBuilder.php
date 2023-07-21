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
        $multiple = FeatureEnum::CONTACTS === $feature;

        return (new FilterCollectionBuilder())
            ->createSelect($multiple ? 'mandateTypes' : 'mandateType', 'Type de mandat')
            ->setChoices(array_flip(MandateTypeEnum::CHOICES))
            ->setMultiple($multiple)
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
