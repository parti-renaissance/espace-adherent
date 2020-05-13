<?php

namespace App\Form;

use App\MediaGenerator\Command\CitizenProjectTractCommand;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectTractType extends CitizenProjectMediaType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('description', TextareaType::class)
            ->add('details', TextareaType::class)
            ->add('download', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectTractCommand::class,
        ]);
    }
}
