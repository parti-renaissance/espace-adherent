<?php

namespace App\Form;

use App\Entity\CoachingRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoachingRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('problem_description', TextareaType::class)
            ->add('proposed_solution', TextareaType::class)
            ->add('required_means', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CoachingRequest::class,
            'error_bubbling' => false,
        ]);
    }
}
