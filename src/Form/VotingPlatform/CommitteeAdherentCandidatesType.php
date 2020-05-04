<?php

namespace AppBundle\Form\VotingPlatform;

use AppBundle\Entity\VotingPlatform\CandidateGroup;
use AppBundle\VotingPlatform\Election\VoteCommand\CommitteeAdherentVoteCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeAdherentCandidatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('womanCandidate', CandidateType::class, [
                'choices' => $this->getFilteredCandidates($options['candidates'], true),
            ])
            ->add('manCandidate', CandidateType::class, [
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
        return array_values(array_filter($candidates, static function (CandidateGroup $group) use ($womenOnly) {
            if ($womenOnly) {
                return current($group->getCandidates())->isWoman();
            }

            return current($group->getCandidates())->isMan();
        }));
    }

    public function getBlockPrefix()
    {
        return 'election_candidates';
    }
}
