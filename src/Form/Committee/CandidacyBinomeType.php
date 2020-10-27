<?php

namespace App\Form\Committee;

use App\Entity\CommitteeCandidacy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidacyBinomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invitation', CommitteeCandidacyInvitationType::class)
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var CommitteeCandidacy $model */
                $model = $event->getData();

                if (!$model->getInvitation()->getMembership()) {
                    $model->setInvitation(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CommitteeCandidacy::class,
                'validation_groups' => ['Default', 'invitation_edit'],
            ])
        ;
    }
}
