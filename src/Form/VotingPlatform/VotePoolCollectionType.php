<?php

namespace App\Form\VotingPlatform;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\VoteChoice;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VotePoolCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('poolChoice', VoteChoiceType::class, [
                'choices' => $this->getFilteredCandidates($options['candidate_groups']),
            ])
            ->add('confirm', SubmitType::class)
            ->add('back', SubmitType::class)
        ;
    }

    private function getFilteredCandidates(array $candidateGroups): array
    {
        $choices = array_map(static function (CandidateGroup $group) {
            return $group->getUuid()->toString();
        }, $candidateGroups);

        $choices[] = VoteChoice::BLANK_VOTE_VALUE;

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['data_class' => VoteCommand::class])
            ->setDefined('candidate_groups')
            ->setRequired('candidate_groups')
            ->setAllowedTypes('candidate_groups', ['array'])
        ;
    }

    public function getBlockPrefix()
    {
        return 'election_candidates';
    }
}
