<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagTranslator;
use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\ScopeEnum;

abstract class AbstractTagsFilterBuilder implements FilterBuilderInterface
{
    protected array $tags;
    protected string $fieldName;
    protected string $fieldLabel;
    protected bool $fullTag = true;

    final public function __construct(private readonly TagTranslator $translator)
    {
        $this->init();
    }

    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect($this->fieldName, $this->fieldLabel)
            ->setFavorite(true)
            ->setChoices($this->getTranslatedChoices())
            ->getFilters()
        ;
    }

    public function getTranslatedChoices(): array
    {
        $choices = [];
        foreach ($this->tags as $tag) {
            $choices[$tag] = $this->translator->trans($tag, $this->fullTag);
        }

        return $choices;
    }

    abstract protected function init(): void;
}
