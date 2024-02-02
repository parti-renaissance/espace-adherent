<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Scope\ScopeEnum;

class AdherentTagsFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TagTranslator $translator)
    {
    }

    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('adherent_tags', 'Labels adhérent')
            ->setChoices($this->getTranslatedChoices())
            ->getFilters()
        ;
    }

    public function getTranslatedChoices(): array
    {
        $choices = [];
        foreach (TagEnum::getAdherentTags() as $tag) {
            $choices[$tag] = $this->translator->trans($tag);
        }

        return $choices;
    }

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
