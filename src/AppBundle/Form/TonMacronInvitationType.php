<?php

namespace AppBundle\Form;

use AppBundle\Entity\TonMacronChoice;
use AppBundle\TonMacron\InvitationProcessor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TonMacronInvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['transition']) {
            case InvitationProcessor::TRANSITION_FILL_INFO:
                $builder
                    ->add('friendFirstName', TextType::class, [
                        'empty_data' => '',
                        'label' => false,
                        'attr' => ['placeholder' => 'ton_macron.invitation_form.friend_first_name'],
                    ])
                    ->add('friendAge', ChoiceType::class, [
                        'label' => false,
                        'placeholder' => 'ton_macron.invitation_form.friend_age',
                        'choices' => array_combine(range(17, 130), range(17, 130)),
                    ])
                    ->add('friendGender', GenderType::class, ['label' => false])
                    ->add('friendPosition', TonMacronChoiceEntityType::class, [
                        'placeholder' => 'ton_macron.invitation_form.friend_position',
                        'step' => TonMacronChoice::STEP_FRIEND_PROFESSIONAL_POSITION,
                    ])
                    ->add(InvitationProcessor::TRANSITION_FILL_INFO, SubmitType::class, [
                        'label' => 'ton_macron.invitation_form.fill_step_1',
                    ])
                ;
                break;
            case InvitationProcessor::TRANSITION_FILL_PROJECT:
                $builder
                    ->add('friendProject', TonMacronChoiceEntityType::class, [
                        'step' => TonMacronChoice::STEP_FRIEND_PROJECT,
                        'expanded' => true,
                    ])
                    ->add(InvitationProcessor::TRANSITION_FILL_PROJECT, SubmitType::class, [
                        'label' => 'ton_macron.invitation_form.fill_step_2',
                    ])
                ;
                break;
            case InvitationProcessor::TRANSITION_FILL_INTERESTS:
                $builder
                    ->add('friendInterests', TonMacronChoiceEntityType::class, [
                        'step' => TonMacronChoice::STEP_FRIEND_INTERESTS,
                        'expanded' => true,
                        'multiple' => true,
                    ])
                    ->add(InvitationProcessor::TRANSITION_FILL_INTERESTS, SubmitType::class, [
                        'label' => 'ton_macron.invitation_form.fill_step_3',
                    ])
                ;
                break;
            case InvitationProcessor::TRANSITION_FILL_REASONS:
                $builder
                    ->add('selfReasons', TonMacronChoiceEntityType::class, [
                        'step' => TonMacronChoice::STEP_SELF_REASONS,
                        'expanded' => true,
                        'multiple' => true,
                    ])
                    ->add(InvitationProcessor::TRANSITION_FILL_REASONS, SubmitType::class, [
                        'label' => 'ton_macron.invitation_form.fill_step_4',
                    ])
                ;
                break;
            case InvitationProcessor::TRANSITION_SEND:
                $builder
                    ->add('messageSubject', TextType::class, [
                        'label' => false,
                        'data' => '',
                        'empty_data' => '',
                    ])
                    ->add('messageContent', TextareaType::class, [
                        'label' => false,
                        'data' => '', // TODO use a service to create default
                        'empty_data' => '',
                    ])
                    ->add('selfFirstName', TextType::class, [
                        'label' => false,
                        'empty_data' => '',
                        'attr' => ['placeholder' => 'ton_macron.invitation_form.self_first_name'],
                    ])
                    ->add('selfLastName', TextType::class, [
                        'label' => false,
                        'empty_data' => '',
                        'attr' => ['placeholder' => 'ton_macron.invitation_form.self_last_name'],
                    ])
                    ->add('selfEmail', EmailType::class, [
                        'label' => false,
                        'empty_data' => '',
                        'attr' => ['placeholder' => 'ton_macron.invitation_form.self_email'],
                    ])
                    ->add('friendEmail', EmailType::class, [
                        'label' => false,
                        'empty_data' => '',
                        'attr' => ['placeholder' => 'ton_macron.invitation_form.friend_email'],
                    ])
                    ->add(InvitationProcessor::TRANSITION_FILL_REASONS, SubmitType::class, [
                        'label' => 'ton_macron.invitation_form.fill_step_5',
                    ])
                ;
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => InvitationProcessor::class,
                'validation_groups' => function (Options $options) {
                    return $options['transition'];
                },
            ])
            ->setRequired('transition')
            ->setAllowedValues('transition', InvitationProcessor::TRANSITIONS)
        ;
    }
}
