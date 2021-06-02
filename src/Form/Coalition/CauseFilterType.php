<?php

namespace App\Form\Coalition;

use App\Coalition\Filter\CauseFilter;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\Coalition;
use App\Form\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CauseFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'required' => false,
                'choices' => Cause::STATUSES,
                'choice_label' => function (string $choice) {
                    return "cause.$choice";
                },
            ])
            ->add('primaryCoalition', EntityType::class, [
                'required' => false,
                'class' => Coalition::class,
            ])
            ->add('secondaryCoalition', EntityType::class, [
                'required' => false,
                'class' => Coalition::class,
            ])
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('authorFirstName', TextType::class, [
                'required' => false,
            ])
            ->add('authorLastName', TextType::class, [
                'required' => false,
            ])
            ->add('createdAfter', DatePickerType::class, [
                'required' => false,
            ])
            ->add('createdBefore', DatePickerType::class, [
                'required' => false,
            ])
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
        ;
    }

    public function getBlockPrefix()
    {
        return 'f';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => CauseFilter::class,
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
            ])
        ;
    }
}
