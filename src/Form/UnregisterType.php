<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

class UnregisterType extends AbstractType
{
    const UNREGISTER_WORD = 'DESADHESION';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label' => 'Pour finaliser la suppression de votre adhésion, saisissez le mot DESADHESION',
                'attr' => ['placeholder' => 'Saisissez ici le mot DESADHESION'],
                'constraints' => [new NotBlank(), new EqualTo(self::UNREGISTER_WORD)],
            ])
        ;
    }

    public function getParent()
    {
        return TextType::class;
    }
}
