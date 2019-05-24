<?php

namespace AppBundle\Form\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\TechnicalSkill;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VolunteerRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('technicalSkills', EntityType::class, [
                'class' => TechnicalSkill::class,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('customTechnicalSkills', TextareaType::class, [
                'required' => false,
            ])
            ->add('isPreviousCampaignMember', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('previousCampaignDetails', TextareaType::class, [
                'required' => false,
            ])
            ->add('shareAssociativeCommitment', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('associativeCommitmentDetails', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', VolunteerRequest::class);
    }

    public function getParent()
    {
        return ApplicationRequestType::class;
    }
}
