<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Scope\FeatureEnum;

class AdherentTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    protected function init(): void
    {
        $this->tags = TagEnum::getAdherentTags();
        $this->fieldName = 'adherent_tags';
        $this->fieldLabel = 'Labels adhérent';
        $this->placeholder = 'Tous mes militants';
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return MilitantFilterGroup::class;
    }

    protected function isRequired(string $scope, ?string $feature): bool
    {
        return FeatureEnum::PUBLICATIONS === $feature;
    }
}
