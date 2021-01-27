<?php

namespace App\Form\Admin;

use App\Committee\CommitteeMandateCommand;
use App\Form\AdherentIdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeMandateCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adherent', AdherentIdType::class, [
                'required' => false,
            ])
            ->add('confirm', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeMandateCommand::class,
        ]);
    }
}
