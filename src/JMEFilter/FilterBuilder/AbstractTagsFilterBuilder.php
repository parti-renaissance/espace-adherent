<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagTranslator;
use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

abstract class AbstractTagsFilterBuilder implements FilterBuilderInterface
{
    protected array $tags;
    protected string $fieldName;
    protected string $fieldLabel;
    protected ?string $placeholder = null;
    protected bool $fullTag = true;

    public function __construct(private readonly TagTranslator $translator)
    {
        $this->init();
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $isRequired = $this->isRequired($scope, $feature);

        $builder = new FilterCollectionBuilder()
            ->createSelect($this->fieldName, $this->fieldLabel)
            ->setFavorite($this->isFavorite($scope, $feature, $isVox))
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS], true))
            ->setChoices($this->getTranslatedChoices())
            ->setRequired($isRequired)
        ;

        if ($this->placeholder) {
            $builder
                ->setPlaceholder($this->placeholder)
                ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature, $this->placeholder)
            ;
        }

        return $builder->getFilters();
    }

    public function getTranslatedChoices(): array
    {
        $choices = [];
        foreach ($this->tags as $tag) {
            $choices[$tag] = $this->translator->trans($tag, $this->fullTag, '_filter_');
        }

        return $choices;
    }

    protected function isRequired(string $scope, ?string $feature): bool
    {
        return false;
    }

    protected function isFavorite(string $scope, ?string $feature, bool $isVox = false): bool
    {
        if (!$isVox) {
            return true;
        }

        return FeatureEnum::CONTACTS !== $feature;
    }

    abstract protected function init(): void;
}
