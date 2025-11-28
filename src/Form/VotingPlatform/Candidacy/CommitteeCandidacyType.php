<?php

declare(strict_types=1);

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\CommitteeCandidacy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeCandidacyType extends AbstractType
{
    public function getParent(): string
    {
        return BaseCandidacyBiographyType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCandidacy::class,
        ]);
    }
}
