<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class AdherentChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('old_password', PasswordType::class, [
            'mapped' => false,
            'constraints' => new UserPassword(['message' => 'adherent.wrong_password']),
        ]);
    }

    public function getParent()
    {
        return AdherentResetPasswordType::class;
    }
}
