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
            ->add('mandates', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => MandateTypeEnum::CHOICES,
            ])
            ->add('politicalFunctions', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => PoliticalFunctionNameEnum::CHOICES,
            ])
            ->add('userListDefinitions', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => UserListDefinitionEnum::CODES_ELECTED_REPRESENTATIVE,
                'choice_label' => function (string $choice) {
                    return $choice;
                },
            ])
            ->add('labels', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => LabelNameEnum::ALL,
                'choice_label' => function (string $choice) {
                    return $choice;
                },
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
