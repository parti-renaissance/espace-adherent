<?php

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\CommitteeCandidacy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeCandidacyType extends AbstractType
{
    public function getParent()
    {
        return BaseCandidacyBiographyType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCandidacy::class,
        ]);
    }
}
