<?php

namespace AppBundle\Form\TypeExtension;

use AppBundle\Form\DataTransformer\NullToStringTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['cast_null_to_string']) {
            $builder->addModelTransformer(new NullToStringTransformer());
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'cast_null_to_string' => false,
            ])
            ->setAllowedTypes('cast_null_to_string', 'bool')
        ;
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
