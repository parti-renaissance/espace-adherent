<?php

namespace App\Form\TerritorialCouncil;

use App\TerritorialCouncil\Candidacy\SearchAvailableMembershipFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchAvailableMembershipType extends AbstractType
{
    public function getBlockPrefix()
    {
        return '';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quality', ChoiceType::class, [
                'choices' => array_combine($options['qualities'], $options['qualities']),
                'required' => false,
            ])
            ->add('query', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
                'data_class' => SearchAvailableMembershipFilter::class,
            ])
            ->setRequired('qualities')
            ->setAllowedTypes('qualities', ['array'])
        ;
    }
}
