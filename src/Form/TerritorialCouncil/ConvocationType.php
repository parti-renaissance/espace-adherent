<?php

namespace App\Form\TerritorialCouncil;

use App\Form\AddressType;
use App\Form\DateTimePickerType;
use App\Form\ManagedPoliticalCommitteeChoiceType;
use App\Form\ManagedTerritorialCouncilChoiceType;
use App\Form\PurifiedTextareaType;
use App\TerritorialCouncil\Convocation\ConvocationObject;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConvocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('territorialCouncil', ManagedTerritorialCouncilChoiceType::class, [
                'required' => false,
                'placeholder' => 'Choisissez un conseil territorial',
            ])
            ->add('politicalCommittee', ManagedPoliticalCommitteeChoiceType::class, [
                'required' => false,
                'placeholder' => 'Choisissez un comité politique',
            ])
            ->add('mode', ChoiceType::class, [
                'choices' => array_combine(DesignationVoteModeEnum::ALL, DesignationVoteModeEnum::ALL),
                'expanded' => true,
                'choice_label' => function (string $choice) {
                    return 'common.mode.'.$choice;
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
                'min_date' => $minDate = new \DateTime('+5 days'),
            ])
            ->add('meetingEndDate', DateTimePickerType::class, [
                'min_date' => $minDate,
            ])
            ->add('description', PurifiedTextareaType::class, [
                'attr' => ['maxlength' => 2000],
                'with_character_count' => true,
                'purify_html_profile' => 'basic_content',
            ])
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();

                // remove political committee fields if choiceList is empty
                if (!$form->get('politicalCommittee')->getConfig()->getAttribute('choice_list')->getChoices()) {
                    $form->remove('politicalCommittee');
                }
            })
        ;

        $builder->get('address')->remove('city');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConvocationObject::class,
        ]);
    }
}
