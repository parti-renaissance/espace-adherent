<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class DeclaredMandateFilterBuilder extends AbstractAdherentMandateFilterBuilder
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $multiple = FeatureEnum::CONTACTS === $feature;

        return new FilterCollectionBuilder()
            ->createSelect($multiple ? 'declaredMandates' : 'declaredMandate', 'Déclaration de mandat')
            ->setChoices($this->getTranslatedChoices())
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->setMultiple($multiple)
            ->getFilters()
        ;
    }
}
