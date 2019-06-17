<?php

namespace AppBundle\Form\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\TechnicalSkill;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Repository\ApplicationRequest\TechnicalSkillRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                'query_builder' => function (TechnicalSkillRepository $technicalSkillRepository) {
                    return $technicalSkillRepository->createDisplayabledQueryBuilder();
                },
                'group_by' => function (TechnicalSkill $technicalSkill) {
                    if ('Autre(s)' !== $technicalSkill->getName()) {
                        return 'Compétences';
                    } else {
                        return 'Autre';
                    }
                },
            ])
            ->add('customTechnicalSkills', TextType::class, [
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
                'with_character_count' => true,
                'attr' => ['maxlength' => 1000],
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
                'with_character_count' => true,
                'attr' => ['maxlength' => 1000],
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
