<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BirthdateType extends AbstractType
{
    public function getParent(): string
    {
        return BirthdayType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $years = range((int) date('Y') - 15, (int) date('Y') - 113);

        $resolver->setDefaults([
            'widget' => 'choice',
            'years' => $years,
            'placeholder' => [
                'year' => 'AAAA',
                'month' => 'MM',
                'day' => 'JJ',
            ],
        ]);
    }
}
