<?php

namespace App\Form\ManagedUsers;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class CandidateManagedUsersFilterType extends AbstractManagedUsersFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('smsSubscription', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'common.all' => null,
                    'common.adherent.subscribed' => true,
                    'common.adherent.unsubscribed' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
        ;
    }
}
