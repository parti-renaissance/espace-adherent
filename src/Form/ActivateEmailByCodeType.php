<?php

declare(strict_types=1);

namespace App\Form;

use App\Adhesion\Request\ValidateAccountRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivateEmailByCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class)
            ->add('emailAddress', RepeatedEmailType::class)
            ->add('validate', SubmitType::class, ['validation_groups' => ['validate-code']])
            ->add('changeEmail', SubmitType::class, ['validation_groups' => ['change-email']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ValidateAccountRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
