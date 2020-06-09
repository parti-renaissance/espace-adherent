<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RequiredCheckboxType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new Assert\IsTrue([
                    'message' => 'common.checkbox.is_true',
                ]),
            ],
        ]);
    }

    public function getParent()
    {
        return CheckboxType::class;
    }
}
