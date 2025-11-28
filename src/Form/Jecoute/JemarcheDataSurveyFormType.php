<?php

declare(strict_types=1);

namespace App\Form\Jecoute;

use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Jecoute\AgeRangeEnum;
use App\Jecoute\GenderEnum;
use App\Jecoute\ProfessionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JemarcheDataSurveyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dataSurvey', DataSurveyFormType::class)
            ->add('lastName', TextType::class, [
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
            ])
            ->add('emailAddress', EmailType::class, [
                'required' => false,
            ])
            ->add('postalCode', TextType::class)
            ->add('profession', ChoiceType::class, [
                'choices' => ProfessionEnum::all(),
            ])
            ->add('ageRange', ChoiceType::class, [
                'choices' => AgeRangeEnum::all(),
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => GenderEnum::all(),
            ])
            ->add('genderOther', TextType::class)
            ->add('agreedToStayInContact', CheckboxType::class)
            ->add('agreedToContactForJoin', CheckboxType::class)
            ->add('agreedToTreatPersonalData', CheckboxType::class)
            ->add('latitude', NumberType::class, [
                'required' => false,
            ])
            ->add('longitude', NumberType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('data_class', JemarcheDataSurvey::class)
        ;
    }
}
