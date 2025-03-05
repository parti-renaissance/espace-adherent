<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\MandateTypeEnum;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\Scope\FeatureEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAdherentMandateFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($feature, [
            FeatureEnum::CONTACTS,
            FeatureEnum::MESSAGES,
            FeatureEnum::MESSAGES_VOX,
        ], true);
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }

    protected function getTranslatedChoices(): array
    {
        $choices = [];
        foreach (MandateTypeEnum::ALL as $mandateType) {
            $choices[$mandateType] = $this->translator->trans("adherent.mandate.type.$mandateType");
        }

        return $choices;
    }
}
