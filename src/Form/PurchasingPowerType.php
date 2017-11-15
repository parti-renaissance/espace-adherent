<?php

namespace AppBundle\Form;

use AppBundle\Entity\PurchasingPowerChoice;
use AppBundle\Interactive\PurchasingPowerProcessor;
use AppBundle\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchasingPowerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['transition']) {
            case PurchasingPowerProcessor::TRANSITION_FILL_INFO:
                $builder
                    ->add('friendFirstName', TextType::class, [
                        'label' => false,
                        'filter_emojis' => true,
                        'attr' => ['placeholder' => 'interactive.form.friend_first_name'],
                    ])
                    ->add('friendAge', ChoiceType::class, [
                        'label' => false,
                        'placeholder' => 'interactive.form.friend_age',
                        'choices' => array_combine(range(17, 130), range(17, 130)),
                    ])
                    ->add('friendGender', GenderType::class, [
                        'label' => false,
                        'choices' => [
                            'interactive.form.male' => Genders::MALE,
                            'interactive.form.female' => Genders::FEMALE,
                        ],
                    ])
                    ->add('friendPosition', PurchasingPowerChoiceEntityType::class, [
                        'placeholder' => 'interactive.form.friend_position',
                        'step' => PurchasingPowerChoice::STEP_FRIEND_PROFESSIONAL_POSITION,
                    ])
                ;
                $this->addSubmitButton($builder, PurchasingPowerProcessor::TRANSITION_FILL_INFO);

                break;
            case PurchasingPowerProcessor::TRANSITION_FILL_CASES:
                $builder
                    ->add('friendCases', PurchasingPowerChoiceEntityType::class, [
                        'step' => PurchasingPowerChoice::STEP_FRIEND_CASES,
                        'expanded' => true,
                        'multiple' => true,
                        'error_bubbling' => true,
                    ])
                ;
                $this->addSubmitButton($builder, PurchasingPowerProcessor::TRANSITION_FILL_CASES);

                break;
            case PurchasingPowerProcessor::TRANSITION_FILL_APPRECIATIONS:
                $builder
                    ->add('friendAppreciations', PurchasingPowerChoiceEntityType::class, [
                        'step' => PurchasingPowerChoice::STEP_FRIEND_APPRECIATIONS,
                        'expanded' => true,
                        'multiple' => true,
                        'error_bubbling' => true,
                    ])
                ;
                $this->addSubmitButton($builder, PurchasingPowerProcessor::TRANSITION_FILL_APPRECIATIONS);

                break;
            case PurchasingPowerProcessor::TRANSITION_SEND:
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
                $this->addSubmitButton($builder, PurchasingPowerProcessor::TRANSITION_SEND);

                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PurchasingPowerProcessor::class,
                'validation_groups' => function (Options $options) {
                    return $options['transition'];
                },
            ])
            ->setRequired('transition')
            ->setAllowedValues('transition', PurchasingPowerProcessor::TRANSITIONS)
        ;
    }

    private function addSubmitButton(FormBuilderInterface $builder, string $step)
    {
        $builder->add($step, SubmitType::class, [
            'label' => 'interactive.form.submit_'.$step,
        ]);
    }
}
