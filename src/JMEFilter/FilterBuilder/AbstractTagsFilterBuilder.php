<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagTranslator;
use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

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
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        $builder = (new FilterCollectionBuilder())
            ->createSelect($this->fieldName, $this->fieldLabel)
            ->setFavorite(true)
            ->setAdvanced(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->setChoices($this->getTranslatedChoices())
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
}
