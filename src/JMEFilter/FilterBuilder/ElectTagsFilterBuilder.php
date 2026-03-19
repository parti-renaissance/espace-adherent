<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class ElectTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    protected function init(): void
    {
        $this->tags = TagEnum::getElectTags();
        $this->fieldName = 'elect_tags';
        $this->fieldLabel = 'Label élu';
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $builder = new FilterCollectionBuilder()
            ->createSelect($this->fieldName, $this->fieldLabel)
            ->setFavorite($this->isFavorite($scope, $feature, $isVox))
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS], true))
            ->setChoices($this->getTranslatedChoices())
            ->setRequired($this->isRequired($scope, $feature))
        ;

        if ($this->placeholder) {
            $builder
                ->setPlaceholder($this->placeholder)
                ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature, $this->placeholder)
            ;
        }

        return $builder->getFilters();
    }
}
