<?php

namespace App\Form;

use App\MediaGenerator\Command\CitizenProjectImageCommand;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectImageType extends CitizenProjectMediaType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('emoji', TextType::class)
            ->add('city')
            ->add('departmentCode')
            ->add('preview', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectImageCommand::class,
        ]);
    }
}
