<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\Scope\FeatureEnum;

class AdherentTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    protected function init(): void
    {
        $this->tags = TagEnum::getAdherentTags();
        $this->fieldName = 'adherent_tags';
        $this->fieldLabel = 'Labels adhérent';
        $this->placeholder = 'Tous mes contacts';
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $filters = parent::build($scope, $feature, $isVox);

        if ($isVox && FeatureEnum::CONTACTS === $feature) {
            foreach ($filters as $filter) {
                $filter->setPosition(1);
            }
        }

        return $filters;
    }

    protected function isRequired(string $scope, ?string $feature): bool
    {
        return true;
    }
}
