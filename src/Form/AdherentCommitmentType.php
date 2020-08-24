<?php

namespace App\Form;

use App\Entity\AdherentCommitment;
use App\Entity\AdherentCommitmentEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentCommitmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commitmentActions', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'choices' => AdherentCommitmentEnum::COMMITMENT_ACTIONS,
                'choice_label' => function ($choice) {
                    return 'adherent_commitment.commitmentActions.'.$choice;
                },
            ])
            ->add('debateAndProposeIdeasActions', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'choices' => AdherentCommitmentEnum::IDEAS_ACTIONS,
                'choice_label' => function ($choice) {
                    return 'adherent_commitment.mdebateAndProposeIdeasActions.'.$choice;
                },
            ])
            ->add('actForTerritoryActions', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'choices' => AdherentCommitmentEnum::ACTS_ACTIONS,
                'choice_label' => function ($choice) {
                    return 'adherent_commitment.actForTerritoryActions.'.$choice;
                },
            ])
            ->add('progressivismActions', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'choices' => AdherentCommitmentEnum::PROGRESSIVISM_ACTIONS,
                'choice_label' => function ($choice) {
                    return 'adherent_commitment.progressivismActions.'.$choice;
                },
            ])
            ->add('skills', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'choices' => AdherentCommitmentEnum::SKILLS,
                'choice_label' => function ($choice) {
                    return 'adherent_commitment.skill.'.$choice;
                },
            ])
            ->add('availability', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'label' => false,
                'choices' => AdherentCommitmentEnum::AVAILABILITIES,
                'choice_label' => function ($choice) {
                    return 'adherent_commitment.availability.'.$choice;
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdherentCommitment::class,
        ]);
    }
}
