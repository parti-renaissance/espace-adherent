<?php

namespace App\Form\Admin;

use App\Form\DataTransformer\StringToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringArrayType extends AbstractType
{
    public function getParent()
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => true,
            'attr' => [
                'rows' => 10,
            ],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new StringToArrayTransformer(\PHP_EOL));
    }
}
