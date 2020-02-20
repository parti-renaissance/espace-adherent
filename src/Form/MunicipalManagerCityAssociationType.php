<?php

namespace AppBundle\Form;

use AppBundle\Form\DataTransformer\CityToInseeCodeTransformer;
use AppBundle\Form\DataTransformer\EmailToAdherentTransformer;
use AppBundle\MunicipalManager\MunicipalManagerAssociationValueObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MunicipalManagerCityAssociationType extends AbstractType
{
    private $adherentTransformer;
    private $cityTransformer;

    public function __construct(
        EmailToAdherentTransformer $adherentTransformer,
        CityToInseeCodeTransformer $cityTransformer
    ) {
        $this->adherentTransformer = $adherentTransformer;
        $this->cityTransformer = $cityTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', HiddenType::class)
            ->add('adherent', EmailType::class, [
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
        $builder->get('adherent')->addModelTransformer($this->adherentTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MunicipalManagerAssociationValueObject::class,
        ]);
    }
}
