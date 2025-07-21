<?php

namespace App\Form\VotingPlatform;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\VoteChoice;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VotePoolCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Designation $designation */
        $designation = $options['designation'];

        if ($designation->isMajorityType()) {
            $builder->add('poolChoice', MajorityVoteChoiceType::class, [
                'candidate_groups' => $options['candidate_groups'],
            ]);
        } else {
            if (!empty($options['candidate_groups'])) {
                $builder->add('poolChoice', VoteChoiceType::class, [
                    'choices' => $this->getFilteredCandidates($designation, $options['candidate_groups']),
                ]);
            } else {
                $builder->add('poolChoice', HiddenType::class, [
                    'data' => -1,
                ]);
            }
        }

        $builder->add('confirm', SubmitType::class);
    }

    private function getFilteredCandidates(Designation $designation, array $candidateGroups): array
    {
        $choices = array_map(static function (CandidateGroup $group) {
            return $group->getUuid()->toString();
        }, $candidateGroups);

        if (!empty($choices) && $designation->isBlankVoteEnabled()) {
            $choices[] = VoteChoice::BLANK_VOTE_VALUE;
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['data_class' => VoteCommand::class])
            ->setDefined(['candidate_groups', 'designation'])
            ->setRequired(['candidate_groups', 'designation'])
            ->setAllowedTypes('candidate_groups', [CandidateGroup::class.'[]'])
            ->setAllowedTypes('designation', [Designation::class])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'election_candidates';
    }
}
