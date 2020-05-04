<?php

namespace AppBundle\Form\VotingPlatform;

use AppBundle\Entity\VotingPlatform\Candidate;
use AppBundle\Entity\VotingPlatform\CandidateGroup;
use AppBundle\VotingPlatform\Election\VoteCommand\VoteCommand;
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
