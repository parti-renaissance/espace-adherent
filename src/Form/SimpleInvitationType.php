<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SimpleInvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, ['constraints' => [
                new NotBlank(),
                new Email(),
                new Length(['max' => 255]),
            ]])
            ->add('message', TextareaType::class, [
                'filter_emojis' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }
}
