<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class DeclaredMandateFilterBuilder extends AbstractAdherentMandateFilterBuilder
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS], true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        $multiple = FeatureEnum::CONTACTS === $feature;

        return new FilterCollectionBuilder()
            ->createSelect($multiple ? 'declaredMandates' : 'declaredMandate', 'Déclaration de mandat')
            ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature)
            ->setChoices($this->getTranslatedChoices())
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->setMultiple($multiple)
            ->getFilters()
        ;
    }
}
