<?php

namespace App\Form\Assessor;

use App\Assessor\Filter\AssociationVotePlaceFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultVotePlaceFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'f';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AssociationVotePlaceFilter::class,
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }
}
