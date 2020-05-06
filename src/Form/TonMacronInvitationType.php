<?php

namespace App\Form;

use App\Entity\TonMacronChoice;
use App\TonMacron\InvitationProcessor;
use App\ValueObject\Genders;
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
                        'label' => false,
                        'filter_emojis' => true,
                        'attr' => ['placeholder' => 'ton_macron.invitation_form.friend_first_name'],
                    ])
                    ->add('friendAge', ChoiceType::class, [
                        'label' => false,
                        'placeholder' => 'ton_macron.invitation_form.friend_age',
                        'choices' => array_combine(range(17, 130), range(17, 130)),
                    ])
                    ->add('friendGender', GenderType::class, [
                        'label' => false,
                        'choices' => [
                            'ton_macron.invitation_form.male' => Genders::MALE,
                            'ton_macron.invitation_form.female' => Genders::FEMALE,
                        ],
                    ])
                    ->add('friendPosition', TonMacronChoiceEntityType::class, [
                        'placeholder' => 'ton_macron.invitation_form.friend_position',
                        'step' => TonMacronChoice::STEP_FRIEND_PROFESSIONAL_POSITION,
                    ])
                ;
                $this->addSubmitButton($builder, InvitationProcessor::TRANSITION_FILL_INFO);

                break;
            case InvitationProcessor::TRANSITION_FILL_PROJECT:
                $builder
                    ->add('friendProject', TonMacronChoiceEntityType::class, [
                        'step' => TonMacronChoice::STEP_FRIEND_PROJECT,
                        'expanded' => true,
                    ])
                ;
                $this->addSubmitButton($builder, InvitationProcessor::TRANSITION_FILL_PROJECT);

                break;
            case InvitationProcessor::TRANSITION_FILL_INTERESTS:
                $builder
                    ->add('friendInterests', TonMacronChoiceEntityType::class, [
                        'step' => TonMacronChoice::STEP_FRIEND_INTERESTS,
                        'expanded' => true,
                        'multiple' => true,
                        'error_bubbling' => true,
                    ])
                ;
                $this->addSubmitButton($builder, InvitationProcessor::TRANSITION_FILL_INTERESTS);

                break;
            case InvitationProcessor::TRANSITION_FILL_REASONS:
                $builder
                    ->add('selfReasons', TonMacronChoiceEntityType::class, [
                        'step' => TonMacronChoice::STEP_SELF_REASONS,
                        'expanded' => true,
                        'multiple' => true,
                        'error_bubbling' => true,
                    ])
                ;
                $this->addSubmitButton($builder, InvitationProcessor::TRANSITION_FILL_REASONS);

                break;
            case InvitationProcessor::TRANSITION_SEND:
                $builder
                    ->add('messageSubject', TextType::class, [
                        'label' => false,
                        'filter_emojis' => true,
                    ])
                    ->add('messageContent', TextareaType::class, [
                        'label' => false,
                        'filter_emojis' => true,
                    ])
                    ->add('selfFirstName', TextType::class, [
                        'label' => false,
                        'filter_emojis' => true,
                    ])
                    ->add('selfLastName', TextType::class, [
                        'label' => false,
                        'filter_emojis' => true,
                    ])
                    ->add('selfEmail', EmailType::class, [
                        'label' => false,
                    ])
                    ->add('friendEmail', EmailType::class, [
                        'label' => false,
                    ])
                ;
                $this->addSubmitButton($builder, InvitationProcessor::TRANSITION_SEND);

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

    private function addSubmitButton(FormBuilderInterface $builder, string $step)
    {
        $builder->add($step, SubmitType::class, [
            'label' => 'ton_macron.invitation_form.submit_'.$step,
        ]);
    }
}
