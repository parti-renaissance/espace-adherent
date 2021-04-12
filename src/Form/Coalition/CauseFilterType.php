<?php

namespace App\Form\Coalition;

use App\Coalition\Filter\CauseFilter;
use App\Entity\Coalition\Cause;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
