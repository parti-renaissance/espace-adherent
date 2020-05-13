<?php

namespace App\Form\VotingPlatform;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\VoteChoice;
use App\VotingPlatform\Election\VoteCommand\CommitteeAdherentVoteCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeAdherentCandidatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('womanCandidate', VoteChoiceType::class, [
                'choices' => $this->getFilteredCandidates($options['candidates'], true),
            ])
            ->add('manCandidate', VoteChoiceType::class, [
                'choices' => $this->getFilteredCandidates($options['candidates'], false),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['data_class' => CommitteeAdherentVoteCommand::class])
            ->setDefined('candidates')
            ->setRequired('candidates')
            ->setAllowedTypes('candidates', ['array'])
        ;
    }

    /**
     * @param CandidateGroup[] $candidates
     *
     * @return CandidateGroup[]
     */
    private function getFilteredCandidates(array $candidates, bool $womenOnly): array
    {
        $candidates = array_filter($candidates, static function (CandidateGroup $group) use ($womenOnly) {
            if ($womenOnly) {
                return current($group->getCandidates())->isWoman();
            }

            return current($group->getCandidates())->isMan();
        });

        $choices = array_map(static function (CandidateGroup $group) {
            return $group->getUuid()->toString();
        }, $candidates);

        $choices[] = VoteChoice::BLANK_VOTE_VALUE;

        return $choices;
    }

    public function getBlockPrefix()
    {
        return 'election_candidates';
    }
}
