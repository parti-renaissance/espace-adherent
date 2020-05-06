<?php

namespace App\Form\VotingPlatform;

use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteCandidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('candidateGroups', ChoiceType::class, [
            'choices' => $options['candidates'],
            'expanded' => true,
            'choice_label' => function (CandidateGroup $candidateGroup) {
                return implode(' / ', array_map(function (Candidate $candidate) {
                    return $candidate->getFullName();
                }, $candidateGroup->getCandidates()));
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => VoteCommand::class,
            ])
            ->setDefined('candidates')
            ->setRequired('candidates')
            ->setAllowedTypes('candidates', ['array'])
        ;
    }
}
