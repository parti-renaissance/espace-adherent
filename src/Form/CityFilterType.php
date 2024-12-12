<?php

namespace App\Form;

use App\AssociationCity\Filter\AssociationCityFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityFilterType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'f';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('inseeCode', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AssociationCityFilter::class,
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }
}
