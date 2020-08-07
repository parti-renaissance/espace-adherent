<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailableForInvitationCandidateType extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => true,
            'multiple' => false,
            'label' => false,
            'class' => TerritorialCouncilMembership::class,
            'choice_label' => 'adherent.fullName',
            'choice_value' => 'uuid',
        ]);
    }
}
