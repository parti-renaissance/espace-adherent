<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

class UnregisterType extends AbstractType
{
    public const UNREGISTER_WORD = 'SUPPRESSION';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'Pour finaliser la suppression de votre adhésion et compte En Marche, saisissez le mot SUPPRESSION',
                'attr' => ['placeholder' => 'Je saisie le mot SUPPRESSION'],
                'constraints' => [new NotBlank(), new EqualTo(self::UNREGISTER_WORD)],
            ])
        ;
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
