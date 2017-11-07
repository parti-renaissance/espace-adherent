<?php

namespace AppBundle\Form;

use AppBundle\Address\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('city', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('country', UnitedNationsCountryType::class, [
                'placeholder' => 'common.country',
            ])
        ;

        $field = $builder->create('postalCode', TextType::class, [
            'error_bubbling' => true,
            'disabled' => !empty($options['postalCode']['disabled']),
        ]);

        $field->addModelTransformer(new CallbackTransformer(
            function ($data) {
                return $data;
            },
            function ($value) {
                return str_replace(' ', '', $value);
            }
        ));

        $builder->add($field);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'translation_domain' => 'messages',
            'error_bubbling' => false,
            'postalCode' => [
                'disabled' => false,
            ]
        ]);
    }
}
