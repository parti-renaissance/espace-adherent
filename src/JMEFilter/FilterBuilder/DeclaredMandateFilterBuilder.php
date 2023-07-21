<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\Membership\MandatesEnum;
use App\Scope\FeatureEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeclaredMandateFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($feature, [
            FeatureEnum::CONTACTS,
            FeatureEnum::MESSAGES,
        ], true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('declaredMandates', 'Déclaration de mandat')
            ->setChoices($this->getTranslatedChoices())
            ->setMultiple(true)
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }

    private function getTranslatedChoices(): array
    {
        $choices = [];
        foreach (MandatesEnum::CHOICES as $transKey => $choice) {
            $choices[$choice] = $this->translator->trans($transKey);
        }

        return $choices;
    }
}
