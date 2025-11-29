<?php

declare(strict_types=1);

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\CommitteeCandidacy;
use App\Form\DoubleNewlineTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommitteeSupervisorCandidacyType extends AbstractType
{
    public function getParent(): string
    {
        return BaseCandidacyBiographyType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('biography', DoubleNewlineTextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 400],
                'constraints' => [new NotBlank(), new Length(['max' => 400])],
            ])
            ->add('faithStatement', DoubleNewlineTextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 1000],
                'constraints' => [new NotBlank(), new Length(['max' => 1000])],
            ])
            ->add('isPublicFaithStatement', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCandidacy::class,
        ]);
    }
}
