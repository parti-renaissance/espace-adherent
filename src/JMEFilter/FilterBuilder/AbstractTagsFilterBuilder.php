<?php

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

    public function supports(string $scope, ?string $feature = null): bool
    {
        return true;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        $isRequired = $this->isRequired($scope, $feature);

        $builder = (new FilterCollectionBuilder())
            ->createSelect($this->fieldName, $this->fieldLabel)
            ->setFavorite(true)
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS], true))
            ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature, $this->placeholder)
            ->setChoices($this->getTranslatedChoices())
            ->setRequired($isRequired)
        ;

        if ($this->placeholder) {
            $builder->setPlaceholder($this->placeholder);
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

    abstract protected function init(): void;

    protected function isRequired(string $scope, ?string $feature): bool
    {
        return false;
    }
}
