<?php

declare(strict_types=1);

namespace App\Form\VotingPlatform;

use App\Entity\VotingPlatform\CandidateGroup;
use App\VotingPlatform\Designation\MajorityVoteMentionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MajorityVoteChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CandidateGroup $candidateGroup */
        foreach ($options['candidate_groups'] as $candidateGroup) {
            $builder->add($candidateGroup->getUuid()->toString(), ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'label' => false,
                'choice_label' => function (string $choice) {
                    return 'voting_platform.vote.majority_vote_mention.'.$choice;
                },
                'constraints' => [new NotBlank()],
                'choices' => array_combine(MajorityVoteMentionEnum::ALL, MajorityVoteMentionEnum::ALL),
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => false,
                'placeholder' => false,
            ])
            ->setDefined('candidate_groups')
            ->setRequired('candidate_groups')
            ->setAllowedTypes('candidate_groups', [CandidateGroup::class.'[]'])
        ;
    }
}
