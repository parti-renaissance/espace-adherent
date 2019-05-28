<?php

namespace AppBundle\Form\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RunningMateRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('curriculum', FileType::class)
            ->add('isLocalAssociationMember', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('localAssociationDomain', TextareaType::class, [
                'required' => false,
            ])
            ->add('isPoliticalActivist', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('politicalActivistDetails', TextareaType::class, [
                'required' => false,
            ])
            ->add('isPreviousElectedOfficial', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('previousElectedOfficialDetails', TextareaType::class, [
                'required' => false,
            ])
            ->add('favoriteThemeDetails', TextareaType::class)
            ->add('projectDetails', TextareaType::class)
            ->add('professionalAssets', TextareaType::class)
            ->add('agreeToDataUse', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', RunningMateRequest::class);
    }

    public function getParent()
    {
        return ApplicationRequestType::class;
    }
}
