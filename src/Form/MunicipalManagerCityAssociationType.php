<?php

namespace AppBundle\Form;

use AppBundle\Form\DataTransformer\CityToInseeCodeTransformer;
use AppBundle\MunicipalManager\MunicipalManagerAssociationValueObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MunicipalManagerCityAssociationType extends AbstractType
{
    private $cityTransformer;

    public function __construct(CityToInseeCodeTransformer $cityTransformer)
    {
        $this->cityTransformer = $cityTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', HiddenType::class)
            ->add('adherent', AdherentEmailType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'E-mail de l\'adhérent',
                    'class' => 'form--full',
                ],
                'invalid_message' => 'city.municipal_manager_association.adherent_not_found',
            ])
        ;

        $builder->get('city')->addModelTransformer($this->cityTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MunicipalManagerAssociationValueObject::class,
        ]);
    }
}
