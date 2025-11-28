<?php

declare(strict_types=1);

namespace App\Form;

use App\Adherent\MandateTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentMandateType extends AbstractType
{
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => MandateTypeEnum::ALL,
            'choice_label' => static function (string $choice): string {
                return "adherent.mandate.type.$choice";
            },
        ]);
    }
}
