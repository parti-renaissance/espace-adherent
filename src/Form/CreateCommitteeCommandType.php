<?php

namespace App\Form;

use App\Address\AddressInterface;
use App\Committee\DTO\CommitteeCreationCommand;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'default_region' => AddressInterface::FRANCE,
                'preferred_country_choices' => [AddressInterface::FRANCE],
            ])
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

    public function getBlockPrefix(): string
    {
        return 'create_committee';
    }

    public function getParent(): ?string
    {
        return CommitteeCommandType::class;
    }
}
