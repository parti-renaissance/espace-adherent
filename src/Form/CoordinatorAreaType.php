<?php

namespace App\Form;

use App\Entity\Committee;
use App\Entity\CoordinatorAreaInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoordinatorAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
        ;

        if (\in_array($options['status'], [CoordinatorAreaInterface::PRE_APPROVED, Committee::PENDING], true)) {
            $builder->add('refuse', SubmitType::class);
        }

        if (\in_array($options['status'], [CoordinatorAreaInterface::PRE_REFUSED, Committee::PENDING], true)) {
            $builder->add('accept', SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'status' => Committee::PENDING,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'coordinator_area';
    }
}
