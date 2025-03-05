<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class MandatesFilterBuilder extends AbstractAdherentMandateFilterBuilder
{
    public function build(string $scope, ?string $feature = null): array
    {
        $multiple = FeatureEnum::CONTACTS === $feature;

        return (new FilterCollectionBuilder())
            ->createSelect($multiple ? 'mandates' : 'mandateType', 'Type de mandat')
            ->setChoices($this->getTranslatedChoices())
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]))
            ->setMultiple($multiple)
            ->getFilters()
        ;
    }
}
