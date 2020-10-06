<?php

namespace App\Form\TerritorialCouncil;

use App\Form\AddressType;
use App\Form\DateTimePickerType;
use App\Form\GenderType;
use App\Form\PurifiedTextareaType;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use App\TerritorialCouncil\Designation\UpdateDesignationRequest;
use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditDesignationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('voteMode', ChoiceType::class, [
                'choices' => array_combine(DesignationVoteModeEnum::ALL, DesignationVoteModeEnum::ALL),
                'expanded' => true,
                'choice_label' => function (string $choice) {
                    return 'designation.vote_mode.'.$choice;
                },
            ])
            ->add('meetingUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('address', AddressType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('meetingStartDate', DateTimePickerType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'min_date' => new \DateTime('+7 days'),
            ])
            ->add('meetingEndDate', DateTimePickerType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'min_date' => new \DateTime('+7 days'),
            ])
            ->add('voteStartDate', DateTimePickerType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'required' => true,
                'min_date' => new \DateTime('+7 days'),
            ])
            ->add('voteEndDate', DateTimePickerType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'min_date' => new \DateTime('+7 days'),
            ])
            ->add('description', PurifiedTextareaType::class, [
                'attr' => ['maxlength' => 2000],
                'filter_emojis' => true,
                'with_character_count' => true,
                'purifier_type' => 'basic_content',
            ])
            ->add('questions', PurifiedTextareaType::class, [
                'required' => false,
                'attr' => ['maxlength' => 2000],
                'filter_emojis' => true,
                'with_character_count' => true,
                'purifier_type' => 'basic_content',
            ])
            ->add('withPoll', ChoiceType::class, [
                'choices' => [
                    'Je ne souhaite pas rééquilibrer la composition du Comité politique' => false,
                    'Je souhaite rééquilibrer la composition du Comité politique' => true,
                ],
                'placeholder' => false,
                'expanded' => true,
                'required' => false,
            ])
            ->add('electionPollGender', GenderType::class, [
                'choices' => [
                    'common.gender.woman' => Genders::FEMALE,
                    'common.gender.man' => Genders::MALE,
                ],
                'placeholder' => false,
                'required' => false,
            ])
            ->add('electionPollChoices', CollectionType::class, [
                'required' => false,
                'entry_type' => IntegerType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'label' => false,
                    'scale' => 0,
                    'data' => 0,
                    'attr' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'class' => 'poll-collection',
                ],
            ])
            ->add('save', SubmitType::class)
        ;

        $builder->get('address')->remove('city');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpdateDesignationRequest::class,
        ]);
    }
}
