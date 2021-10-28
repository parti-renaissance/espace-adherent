<?php

namespace App\Form\TerritorialCouncil;

use App\Form\AddressType;
use App\Form\DateTimePickerType;
use App\Form\PurifiedTextareaType;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use App\TerritorialCouncil\Designation\UpdateDesignationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditDesignationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $minDate = new \DateTime('+7 days');
        $maxDate = new \DateTime('+3 months');

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
                'min_date' => $minDate,
                'max_date' => $maxDate,
            ])
            ->add('meetingEndDate', DateTimePickerType::class, [
                'min_date' => $minDate,
            ])
            ->add('voteStartDate', DateTimePickerType::class, [
                'min_date' => $minDate,
                'max_date' => $maxDate,
            ])
            ->add('voteEndDate', DateTimePickerType::class, [
                'min_date' => $minDate,
            ])
            ->add('description', PurifiedTextareaType::class, [
                'attr' => ['maxlength' => 2000],
                'with_character_count' => true,
                'purifier_type' => 'basic_content',
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
