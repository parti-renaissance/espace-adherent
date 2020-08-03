<?php

namespace App\Form\AdherentMessage;

use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\UserListDefinitionEnum;
use App\Form\GenderType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectedRepresentativeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('mandate', ChoiceType::class, [
                'required' => false,
                'choices' => MandateTypeEnum::CHOICES,
                'choice_label' => function (string $choice) {
                    return "elected_representative.mailchimp_tag.$choice";
                },
            ])
            ->add('politicalFunction', ChoiceType::class, [
                'required' => false,
                'choices' => PoliticalFunctionNameEnum::CHOICES,
                'choice_label' => function (string $choice) {
                    return "elected_representative.mailchimp_tag.$choice";
                },
            ])
            ->add('userListDefinition', ChoiceType::class, [
                'required' => false,
                'choices' => UserListDefinitionEnum::CODES_ELECTED_REPRESENTATIVE,
                'choice_label' => function (string $choice) {
                    return "elected_representative.mailchimp_tag.$choice";
                },
            ])
            ->add('label', ChoiceType::class, [
                'required' => false,
                'choices' => LabelNameEnum::ALL,
                'choice_label' => function (string $choice) {
                    return "elected_representative.mailchimp_tag.$choice";
                },
            ])
            ->add('isAdherent', ChoiceType::class, [
                'placeholder' => 'Tous',
                'choices' => [
                    'global.yes' => true,
                    'global.no' => false,
                ],
                'expanded' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'referent_tags' => null,
            ])
            ->setAllowedTypes('referent_tags', ['array'])
            ->setRequired(['referent_tags'])
        ;
    }
}
