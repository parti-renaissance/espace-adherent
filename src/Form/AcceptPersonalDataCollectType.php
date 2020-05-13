<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class AcceptPersonalDataCollectType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'constraints' => new IsTrue([
                'message' => 'common.personal_data_collection.required',
            ]),
        ]);
    }

    public function getParent(): string
    {
        return CheckboxType::class;
    }
}
