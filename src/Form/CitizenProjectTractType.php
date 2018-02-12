<?php

namespace AppBundle\Form;

use AppBundle\MediaGenerator\Command\CitizenProjectTractCommand;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectTractType extends CitizenProjectMediaType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('description', TextareaType::class)
            ->add('details', TextareaType::class)
            ->add('download', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectTractCommand::class,
        ]);
    }
}
