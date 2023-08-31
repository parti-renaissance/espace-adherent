<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\MandateTypeEnum;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\Scope\FeatureEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class MandatesFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, string $feature = null): bool
    {
        return FeatureEnum::ELECTED_REPRESENTATIVE === $feature;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('mandates', 'Mandats')
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
        foreach (MandateTypeEnum::ALL as $mandateType) {
            $choices[$mandateType] = $this->translator->trans("adherent.mandate.type.$mandateType");
        }

        return $choices;
    }
}
