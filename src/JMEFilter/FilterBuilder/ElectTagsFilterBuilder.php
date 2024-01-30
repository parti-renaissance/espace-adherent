<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\Scope\ScopeEnum;

class ElectTagsFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TagTranslator $translator)
    {
    }

    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('elect_tags', 'Labels élu')
            ->setChoices($this->getTranslatedChoices())
            ->getFilters()
        ;
    }

    public function getTranslatedChoices(): array
    {
        $choices = [];
        foreach (TagEnum::getElectTags() as $tag) {
            $choices[$tag] = $this->translator->trans($tag);
        }

        return $choices;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
