<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\MandateTypeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAdherentMandateFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
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
