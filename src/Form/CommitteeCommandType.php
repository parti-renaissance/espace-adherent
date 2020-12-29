<?php

namespace App\Form;

use App\Address\Address;
use App\Committee\CommitteeCommand;
use App\Form\Committee\ProvisionalSupervisorType;
use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $command = $builder->getData();
        $committee = $command instanceof CommitteeCommand ? $command->getCommittee() : null;
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
                'disabled' => $committee ? $committee->isNameLocked() : false,
            ])
            ->add('description', TextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 140],
                'filter_emojis' => true,
            ])
            ->add('address', AddressType::class, [
                'disable_fields' => $committee ? $committee->isApproved() : false,
                'child_error_bubbling' => false,
                'data' => $builder->getData() ? Address::createFromAddress($builder->getData()->getAddress()) : null,
                'disabled' => $committee ? $committee->isNameLocked() : false,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCommand::class,
            'with_provisional' => false,
            'with_social_networks' => false,
        ])
            ->setAllowedTypes('with_provisional', ['bool'])
            ->setAllowedTypes('with_social_networks', ['bool'])
        ;
    }

    public function getBlockPrefix()
    {
        return 'committee';
    }
}
