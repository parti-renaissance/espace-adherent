<?php

declare(strict_types=1);

namespace App\Form\Committee;

use App\Entity\CommitteeCandidacyInvitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeCandidacyInvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('membership', CommitteeCandidacyInvitedMembershipType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCandidacyInvitation::class,
        ]);
    }
}
