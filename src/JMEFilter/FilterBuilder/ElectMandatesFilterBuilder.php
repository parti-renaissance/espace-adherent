<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class ElectMandatesFilterBuilder extends AbstractAdherentMandateFilterBuilder
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createSelect(FeatureEnum::CONTACTS === $feature ? 'electMandates' : 'electMandate', 'Mandats')
            ->setChoices($this->getTranslatedChoices())
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->setMultiple(FeatureEnum::CONTACTS === $feature)
            ->setHelp('Les mandats sont ajoutés et mis à jour par le pôle élections et les Assemblées départementales ou FDE.')
            ->getFilters()
        ;
    }
}
