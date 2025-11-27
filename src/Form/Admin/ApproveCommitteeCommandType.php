<?php

namespace App\Form\Admin;

use App\Address\Address;
use App\Committee\DTO\CommitteeCommand;
use App\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApproveCommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $command = $builder->getData();
        $committee = $command instanceof CommitteeCommand ? $command->getCommittee() : null;
        $builder
            ->add('name', TextType::class, [
                'format_title_case' => true,
            ])
            ->add('slug', TextType::class, [
                'help' => 'Généré automatiquement depuis le titre.',
            ])
            ->add('description', TextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 140],
            ])
            ->add('address', AddressType::class, [
                'disable_fields' => $committee ? $committee->isApproved() : false,
                'data' => $builder->getData() ? Address::createFromAddress($builder->getData()->getAddress()) : null,
            ])
            ->add('nameLocked', CheckboxType::class, [
                'required' => false,
            ])
            ->add('confirm', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCommand::class,
        ]);
    }
}
