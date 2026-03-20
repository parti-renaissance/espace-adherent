<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class DeclaredMandateFilterBuilder extends AbstractAdherentMandateFilterBuilder
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createSelect(FeatureEnum::CONTACTS === $feature ? 'declaredMandates' : 'declaredMandate', 'Déclarations de mandats')
            ->setChoices($this->getTranslatedChoices())
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->setMultiple(FeatureEnum::CONTACTS === $feature)
            ->setHelp('Les déclarations de mandats sont saisie par l’utilisateur à son inscription. Elles ne sont pas fiables et ne servent qu’à détecter d’éventuelles création de compte par des élus.')
            ->getFilters()
        ;
    }
}
