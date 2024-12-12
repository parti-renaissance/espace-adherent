<?php

namespace App\Form;

use App\Address\Address;
use App\Committee\DTO\CommitteeCommand;
use App\Form\Committee\ProvisionalSupervisorType;
use App\Validator\AddressInManagedZones;
use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $command = $builder->getData();
        $committee = $command instanceof CommitteeCommand ? $command->getCommittee() : null;
        $builder
            ->add('name', TextType::class, [
                'format_title_case' => true,
                'disabled' => $committee ? $committee->isNameLocked() : false,
            ])
            ->add('description', TextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 140],
            ])
            ->add('address', AddressType::class, [
                'child_error_bubbling' => false,
                'data' => $builder->getData() ? Address::createFromAddress($builder->getData()->getAddress()) : null,
                'disabled' => $committee ? $committee->isNameLocked() : false,
                'constraints' => isset($options['space_type']) ? [
                    new AddressInManagedZones($options['space_type']),
                ] : [],
            ])
        ;

        if ($options['with_social_networks']) {
            $builder
                ->add('facebookPageUrl', UrlType::class, [
                    'required' => false,
                    'default_protocol' => null,
                ])
                ->add('twitterNickname', TextType::class, [
                    'required' => false,
                ])
            ;
        }

        if ($options['with_provisional']) {
            $builder
                ->add('provisionalSupervisorMale', ProvisionalSupervisorType::class, [
                    'gender' => Genders::MALE,
                    'error_bubbling' => false,
                ])
                ->add('provisionalSupervisorFemale', ProvisionalSupervisorType::class, [
                    'gender' => Genders::FEMALE,
                    'error_bubbling' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCommand::class,
            'with_provisional' => false,
            'with_social_networks' => false,
            'space_type' => null,
        ])
            ->setAllowedTypes('with_provisional', ['bool'])
            ->setAllowedTypes('with_social_networks', ['bool'])
            ->setAllowedTypes('space_type', ['string', 'null'])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'committee';
    }
}
