<?php

declare(strict_types=1);

namespace App\Form\Admin\Adherent;

use App\Renaissance\Membership\Admin\AdherentCreateCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerifyEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentCreateCommand::class,
                'validation_groups' => 'admin_adherent_renaissance_verify_email',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'adherent_create';
    }
}
