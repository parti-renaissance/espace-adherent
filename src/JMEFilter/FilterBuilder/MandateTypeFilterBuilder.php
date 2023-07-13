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
            FeatureEnum::ELECTED_REPRESENTATIVE,
            FeatureEnum::MESSAGES,
            FeatureEnum::CONTACTS,
        ], true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('mandateType', 'Type de mandat')
            ->setChoices(array_flip(
                FeatureEnum::ELECTED_REPRESENTATIVE === $feature
                    ? MandateTypeEnum::TYPE_CHOICES
                    : MandateTypeEnum::TYPE_CHOICES_CONTACTS
            ))
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
