<?php

namespace App\Form;

use App\Validator\Repeated;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepeatedEmailType extends AbstractType
{
    public function getParent(): ?string
    {
        return RepeatedType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type' => EmailType::class,
            'invalid_message' => 'common.email.repeated',
            'options' => [
                'constraints' => [
                    new Repeated([
                        'message' => 'common.email.repeated',
                        'groups' => ['Registration', 'Update'],
                    ]),
                ],
            ],
        ]);
    }
}
