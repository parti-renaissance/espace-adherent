<?php

namespace App\Form;

use App\Committee\CommitteeCreationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('acceptConfidentialityTerms', CheckboxType::class, [
                'required' => false,
            ])
            ->add('acceptContactingTerms', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCreationCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'create_committee';
    }

    public function getParent()
    {
        return CommitteeCommandType::class;
    }
}
