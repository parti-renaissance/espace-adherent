<?php

namespace AppBundle\Form;

use AppBundle\MunicipalManager\Filter\AssociationCityFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentCityFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'f';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('inseeCode', TextType::class, [
                'required' => false,
            ])
            ->add('municipalManagerFirstName', TextType::class, [
                'required' => false,
            ])
            ->add('municipalManagerLastName', TextType::class, [
                'required' => false,
            ])
            ->add('municipalManagerEmail', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AssociationCityFilter::class,
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }
}
