<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagFilterEnum;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElectTagsFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true) && FeatureEnum::CONTACTS === $feature;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('elect_tags', 'Tags élu')
            ->setMultiple(true)
            ->setChoices($this->getTranslatedChoices())
            ->getFilters()
        ;
    }

    public function getTranslatedChoices(): array
    {
        $choices = [];
        foreach (TagFilterEnum::getFiltersTags() as $tag) {
            $choices[$tag] = $this->translator->trans('adherent.filter_tag.'.$tag);
        }

        return $choices;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
