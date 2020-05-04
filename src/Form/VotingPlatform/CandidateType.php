<?php

namespace AppBundle\Form\VotingPlatform;

use AppBundle\Entity\VotingPlatform\Candidate;
use AppBundle\Entity\VotingPlatform\CandidateGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateType extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => CandidateGroup::class,
            'expanded' => true,
            'multiple' => false,
            'choice_value' => static function (?CandidateGroup $candidateGroup) {
                return $candidateGroup ? $candidateGroup->getUuid()->toString() : null;
            },
            'choice_label' => static function (CandidateGroup $candidateGroup) {
                return implode(
                    ' / ',
                    array_map(
                        static function (Candidate $candidate) {
                            return $candidate->getFullName();
                        },
                        $candidateGroup->getCandidates()
                    )
                );
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'election_candidate';
    }
}
