<?php

declare(strict_types=1);

namespace App\Form;

use App\Address\NullableAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NullableAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'required' => false,
            ])
            ->add('city', HiddenType::class, [
                'required' => false,
            ])
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('country', ReCountryType::class, [
                'required' => false,
            ])
        ;

        $field = $builder->create('postalCode', TextType::class, [
            'required' => false,
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NullableAddress::class,
            'error_bubbling' => false,
        ]);
    }
}
