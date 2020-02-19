<?php

namespace AppBundle\Form;

use AppBundle\Assessor\AssessorRoleAssociationValueObject;
use AppBundle\Form\DataTransformer\EmailToAdherentTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessorRoleAssociationType extends AbstractType
{
    private $adherentTransformer;

    public function __construct(EmailToAdherentTransformer $adherentTransformer)
    {
        $this->adherentTransformer = $adherentTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('votePlace', AssociationVotePlaceType::class)
            ->add('adherent', EmailType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'E-mail de l\'adhérent',
                    'class' => 'form--full',
                ],
                'invalid_message' => 'assessor.adherent_association.adherent_not_found',
            ])
        ;

        $builder->get('adherent')->addModelTransformer($this->adherentTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AssessorRoleAssociationValueObject::class,
        ]);
    }
}
